<?php
namespace Resaname\AI;
use Resaname\Config;
final class IsmcProvider {
    private ?string $model;
    public function __construct(?string $model=null) { $this->model=$model; }
    public function complete(string $system,string $user): array {
        if (!Config::bool('AI_ENABLED')) throw new \RuntimeException('AI provider is disabled until API contract is verified.');
        $base=rtrim((string)Config::get('AI_BASE_URL'),'/');
        $path=Config::get('AI_CHAT_PATH');
        $key=Config::get('AI_API_KEY');
        if (!$path || !$key) throw new \RuntimeException('AI endpoint path or API key is not configured.');
        $url=$base.'/'.ltrim($path,'/');
        $payload=['model'=>$this->model?:Config::get('AI_MODEL','qwen38'),'messages'=>[
            ['role'=>'system','content'=>$system],['role'=>'user','content'=>$user]
        ],'temperature'=>0.2];
        // گواهی self-signed (API داخلی ISMC) — با AI_SSL_VERIFY=false اعتبارسنجی گواهی غیرفعال می‌شود
        $sslVerify=Config::bool('AI_SSL_VERIFY',true);
        $ch=curl_init($url);
        curl_setopt_array($ch,[
            CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>15,
            CURLOPT_TIMEOUT=>(int)Config::get('AI_TIMEOUT','90'),
            CURLOPT_SSL_VERIFYPEER=>$sslVerify,
            CURLOPT_SSL_VERIFYHOST=>$sslVerify?2:0,
            CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$key],
            CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)
        ]);
        $body=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $err=curl_error($ch); curl_close($ch);
        if ($body===false || $status<200 || $status>=300) throw new \RuntimeException("AI HTTP error $status: ".substr((string)$err.' '.(string)$body,0,1000));
        $decoded=json_decode($body,true);
        if (!is_array($decoded)) throw new \RuntimeException('Invalid JSON response from AI provider');
        return $decoded;
    }
}
