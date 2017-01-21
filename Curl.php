<?php
namespace DedyCo\Curl;

class Curl
{

    public $cookie = 'cookie';

    public $referer = '';

    public $posts = null;

    public $post_string = '';

    public $url = '';

    public $header = false;

    public $httpheader = array();

    public $user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)';

    public $timeout = 30;

    public $multipart = false;

    public $ch;

    public $proxy;

    public $proxy_type = 'http';

    public $content;

    public $info;

    public $errno;

    public $error;

    public $file = '';

    public $fp;

    function exec()
    {
        if ($this->file != '') {
            $this->fp = fopen($this->file, 'w+');
        }
        
        $this->ch = curl_init();
        
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        
        curl_setopt($this->ch, CURLOPT_HEADER, $this->header);
        
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout); // error:56
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie);
        
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpheader);
        
        // curl_setopt($this->ch, CURLINFO_HEADER_OUT, 1);
        // curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        
        if ($this->referer != '')
            curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);
        
        if (sizeof($this->posts) > 0) {
            if ($this->multipart == true) {
                $this->post_string = $this->posts;
            } else {
                $this->post_string = http_build_query($this->posts, '', '&');
            }
            
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_string);
        }
        
        if ($this->proxy != '') {
            curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy);
            curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, 1);
            if ($this->proxy_type == 'socks5')
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        
        if ($this->file != '') {
            curl_setopt($this->ch, CURLOPT_FILE, $this->fp);
        }
        
        $content = curl_exec($this->ch);
        $info = curl_getinfo($this->ch);
        
        $header_response = array();
        $header_response['raw'] = '';
        $header_response['parts'] = array();
        if ($this->header == true) {
            $raw = substr($content, 0, $info['header_size']);
            $header_response['raw'] = $raw;
            $content = substr($content, $info['header_size'], $info['size_download']);
            
            $raw = trim($raw);
            $lines = explode("\r", $raw);
            
            $i = 0;
            $j = 0;
            $parts = array();
            foreach ($lines as $line) {
                $line = trim($line);
                // pr($line);
                if (preg_match("#^([^:]+):(.*)$#", $line, $match)) {
                    $k = trim($match[1]);
                    $v = trim($match[2]);
                    $parts[$i][$k] = trim($v);
                } else {
                    // pr($line);
                    $parts[$i]['response'] = $line;
                }
                
                if ($line == '')
                    $i ++;
            }
            
            // pr($parts);exit;
            $header_response['parts'] = $parts;
        }
        $info['header_response'] = $header_response;
        $this->content = $content;
        $this->info = $info;
        $this->errno = curl_errno($this->ch);
        $this->error = curl_error($this->ch);
        
        curl_close($this->ch);
        
        if ($this->file != '') {
            fclose($this->fp);
        }
    }
}

?>
