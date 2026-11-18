<?php
namespace Resaname;
use PDO;
final class SyncService {
    private const MAP = [
        'cards'=>['id','code','slug','title_fa','category_id','status','updated_at'],
        'theorists'=>['id','name','slug'],
        'library_resources'=>['id','slug','resource_type','title','status','updated_at']
    ];
    public function __construct(private PDO $main, private PDO $editorial) {}
    public function sync(string $table, int $pageSize=500): int {
        if (!isset(self::MAP[$table])) throw new \InvalidArgumentException('Unsupported source table');
        $pageSize=max(1,min($pageSize,1000));
        $count=0; $lastId=null;
        $up=$this->editorial->prepare(
            "INSERT INTO editorial_documents
            (source_table,source_id,title,source_status,source_updated_at,original_json,original_hash,current_json,current_hash,workflow_status,created_at,updated_at)
            VALUES (:t,:id,:title,:status,:source_updated,:original,:hash,:current,:current_hash,'imported',NOW(),NOW())
            ON DUPLICATE KEY UPDATE
              title=VALUES(title), source_status=VALUES(source_status),
              source_updated_at=VALUES(source_updated_at),
              original_json=IF(workflow_status IN ('imported','queued'),VALUES(original_json),original_json),
              original_hash=IF(workflow_status IN ('imported','queued'),VALUES(original_hash),original_hash),
              updated_at=NOW()"
        );
        do {
            if ($lastId === null) {
                $stmt=$this->main->query('SELECT * FROM `'.$table.'` ORDER BY `id` LIMIT '.$pageSize);
            } else {
                $stmt=$this->main->prepare('SELECT * FROM `'.$table.'` WHERE `id` > ? ORDER BY `id` LIMIT '.$pageSize);
                $stmt->execute([$lastId]);
            }
            $rows=$stmt->fetchAll();
            foreach ($rows as $row) {
                $json=json_encode($row,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                $hash=hash('sha256',$json);
                $title=$row['title_fa'] ?? $row['title'] ?? $row['name'] ?? '(بدون عنوان)';
                $up->execute([
                    ':t'=>$table, ':id'=>(string)$row['id'], ':title'=>$title,
                    ':status'=>$row['status'] ?? null, ':source_updated'=>$row['updated_at'] ?? null,
                    ':original'=>$json, ':hash'=>$hash, ':current'=>$json, ':current_hash'=>$hash
                ]);
                $lastId=(string)$row['id']; $count++;
            }
        } while (count($rows)===$pageSize);
        return $count;
    }
}
