<?php
namespace Tfs;

/**
 * TFS Rest Web API php 客户端
 *
 * <code>
 *  $client = new Tfs\Client($appkey);
 *  $response = $client->save($fileContent);
 *  if ( $response->isOk() ) {
 *      $tfsFile = $response->getResult()->TFS_FILE_NAME;
 *      echo "成功写入 Tfs 文件：" . $tfsFile;
 *      $response = $client->fetch($tfsFile);
 *      if ( $response->isOk() ) {
 *          echo "Tfs 文件内容：" . $response->getContent();
 *      }
 *  } else {
 *      error_log(sprintf("写入错误 [%s]: %s\n", $response->getCode(), $response->getMessage()));
 *  }
 * </code>
 */
class Client
{
    private $rootServer = 'restful-store.daily.tbsite.net:3800';
    private $appKey;
    private $appId;
    private $uid = 1;
    private $httpClient;

    protected $accessCount = 0;
    protected $servers;
    protected $maxAccessCount;
    
    public function __construct($appKey, $rootServer=null)
    {
        $this->appKey = $appKey;
        if ( isset($rootServer) ) {
            $this->setRootServer($rootServer);
        }
    }
    
    public function getAppKey()
    {
        return $this->appKey;
    }

    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
        return $this;
    }

    public function getAppId()
    {
        if ( !isset($this->appId) ) {
            $this->appId = $this->fetchAppId();
        }
        return $this->appId;
    }

    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    public function getRootServer()
    {
        return $this->rootServer;
    }

    public function setRootServer($rootServer)
    {
        if ( substr($rootServer, 0, 7) === 'http://' ) {
            $rootServer = substr($rootServer, 7);
        }
        $this->rootServer = $rootServer;
        return $this;
    }

    public function getHttpClient()
    {
        if ( !$this->httpClient ) {
            $this->httpClient = new HttpClient;
        }
        return $this->httpClient;
    }

    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }
    
    /**
     * 获取原生 Tfs 文件
     * 
     * @param string $tfsFile
     * @param array $options 可选选项
     *  <ul>
     *  <li> suffix
     *  <li> offset
     *  <li> size
     *  </ul>
     * @return HttpResponse
     */
    public function fetch($tfsFile, $options=null)
    {
        return $this->getHttpClientWithHeaders()->get($this->buildUrl($tfsFile, $options));
    }

    /**
     * 获取原生 Tfs 文件并保存到本地文件
     *
     * @param string $tfsFile
     * @param string $localFile
     * @param array $options @see fetch()
     * @return boolean
     */
    public function fetchFile($tfsFile, $localFile, $options=null)
    {
        $response = $this->fetch($tfsFile, $options);
        if ( $response->isOK() ) {
            return file_put_contents($localFile, $response->getContent());
        }
    }
    
    /**
     * 写入原生 Tfs 文件
     *
     * @param string $content 文件内容
     * @param array $options
     *  <ul>
     *  <li> suffix
     *  <li> simple_name
     *  <li> large_file
     *  </ul>
     * @return HttpResponse
     */
    public function save($content, $options=null)
    {
        return $this->getHttpClientWithHeaders()
            ->post($this->buildUrl('', $options), $content);
    }

    /**
     * 将本地文件写入原生 Tfs 文件
     * @param string $localFile
     * @param array $options @see save()
     * @return HttpResponse
     * @throws RuntimeException 当本地文件不存在或无法读取时
     */
    public function saveFile($localFile, $options=null)
    {
        $content = file_get_contents($localFile);
        if ( false === $content ) {
            throw new \RuntimeException("无法读取文件 '$localFile'");
        }
        return $this->save($content, $options);
    }

    /**
     * 删除原生 Tfs 文件
     *
     * @param string $tfsFile
     * @param array $options
     * <ul>
     *  <li> suffix
     *  <li> hide
     * </ul>
     * @return HttpResponse
     */
    public function unlink($tfsFile, $options=null)
    {
        return $this->getHttpClientWithHeaders()->delete($this->buildUrl($tfsFile, $options));
    }

    /**
     * 隐藏原生 Tfs 文件
     * @param string $tfsFile
     * @param array $options
     * <ul>
     * <li> suffix
     * </ul>
     * @return HttpResponse
     */
    public function hide($tfsFile, $options=null)
    {
        if ( is_array($options) ) {
            $options['hide'] = 1;
        } else {
            $options = array('hide' => 1);
        }
        return $this->unlink($tfsFile, $options);
    }

    /**
     * 获取原生 Tfs 文件信息
     * @param string $tfsFile
     * @param array $options
     * <ul>
     * <li> suffix
     * <li> type 是否强制获取 文件删除或隐藏时无法获取
     * </ul>
     * @return HttpResponse
     */
    public function stat($tfsFile, $options=null)
    {
        return $this->getHttpClientWithHeaders()->get($this->buildUrl('metadata/'.$tfsFile, $options));
    }
    
    /**
     * 获取应用 appid
     * @throws RuntimeException
     */
    public function fetchAppId()
    {
        $response = $this->getHttpClientWithHeaders()->get('/v2/'.$this->appKey.'/appid');
        if ( $response->isOK() ) {
            return $response->getResult()->APP_ID;
        } else {
            throw new \RuntimeException("获取APPID错误: ". $response->getMessage(), $response->getCode());
        }
    }

    /**
     * 创建自定义文件
     * 
     * @param string $file
     * @return HttpResponse
     */
    public function create($file)
    {
        return $this->getHttpClientWithHeaders()->post($this->buildFileUrl($file));
    }

    /**
     * 写入自定义文件
     *
     * @param string $file tfs 文件名
     * @param string $content 文件内容
     * @param int $offset 
     * @return HttpResponse
     */
    public function write($file, $content, $offset=null)
    {
        return $this->getHttpClientWithHeaders()
            ->put($this->buildFileUrl($file, isset($offset) ? array('offset' => $offset) : null), $content);
    }

    /**
     * 本地文件写入自定义文件
     * @param string $file tfs 文件名
     * @param string $localFile
     * @param HttpResponse
     */
    public function writeFile($file, $localFile)
    {
        $content = file_get_contents($localFile);
        if ( false === $content ) {
            throw new \RuntimeException("无法读取文件 '$localFile'");
        }
        return $this->write($file, $content);
    }

    /**
     * 读取自定义文件
     * @param string $file
     * @param array $options
     * <ul>
     * <li> offset
     * <li> size
     * <li> appid
     * <li> uid
     * </ul>
     * @return HttpResponse
     */
    public function read($file, $options=null)
    {
        return $this->getHttpClientWithHeaders()->get($this->buildFileUrl($file, $options));
    }

    /**
     * 读取自定义文件到本地文件
     * @param string $file
     * @param string $localFile
     * @param array $options
     * <ul>
     * <li> offset
     * <li> size
     * <li> appid
     * <li> uid
     * </ul>
     * @return HttpResponse
     */
    public function readFile($file, $localFile, $options=null)
    {
        $response = $this->read($file, $options);
        if ( $response->isOk() ) {
            return file_put_contents($localFile, $response->getContent());
        }
    }
    
    /**
     * 删除自定义文件
     * @param string $file
     * @return HttpResponse
     */
    public function delete($file)
    {
        return $this->getHttpClientWithHeaders()->delete($this->buildFileUrl($file));
    }

    /**
     * 删除自定义目录
     * @param string $dir
     * @return HttpResponse
     */
    public function deleteDir($dir)
    {
        return $this->getHttpClientWithHeaders()->delete($this->buildFileUrl($dir, null, 'dir'));
    }
    
    /**
     * 检查文件是否存在
     * @param string $file
     * @param array $options
     * <ul>
     * <li> appid
     * <li> uid
     * </ul>
     * @return HttpResponse
     */
    public function fileExist($file, $options=null)
    {
        return $this->getHttpClientWithHeaders()->head($this->buildFileUrl($file));
    }

    /**
     * 判断目录是否存在
     * @param string $dir
     * @param array $options
     * @return HttpResponse
     */
    public function dirExist($dir, $options=null)
    {
        return $this->getHttpClientWithHeaders()->head($this->buildFileUrl($dir, $options, 'dir'));
    }
    
    /**
     * 获取自定义文件信息
     * @param string $file
     * @param array $options
     * @return HttpResponse
     */
    public function fileinfo($file, $options=null)
    {
        return $this->getHttpClientWithHeaders()->get($this->buildFileUrl($file, $options, 'file', true));
    }

    /**
     * 获取自定义文件目录信息
     * @param string $dir
     * @param array $options
     * @return HttpResponse
     */
    public function dirinfo($dir, $options=null)
    {
        return $this->getHttpClientWithHeaders()->get($this->buildFileUrl($dir, $options, 'dir', true));
    }

    protected function buildUrl($path, $options=null)
    {
        return '/v1/' . $this->appKey
            . (empty($path) ? '' : '/'.$path)
            . (empty($options) ? '' : '?'.http_build_query($options));
    }

    /**
     * 构造自定义文件 URL
     * @param string $path
     * @param array $options
     * @param string $type 可选类型：file, dir
     */
    protected function buildFileUrl($path, $options=null, $type='file', $is_metadata=false)
    {
        $appId = isset($options['appid']) ? $options['appid'] : $this->getAppId();
        $uid = isset($options['uid']) ? $options['uid'] : $this->getUid();
        $metadata = ($is_metadata ? '/metadata' : '');
        return '/v2/'. $this->appKey . $metadata . '/'. $appId . '/' . $uid . '/' . $type . '/'. ltrim($path, '/')
            . (empty($options) ? '' : '?'.http_build_query($options));
    }
    
    protected function getHttpClientWithHeaders($headers=null, $server=null)
    {
        $client = $this->getHttpClient();
        if ( null === $server ) {
            $server = $this->getServer();
        }
        $defaultHeaders = array(
            'Date' => date('D,j M Y H:i:s ').'GMT'
        );
        $client->setBaseUrl('http://'.$server)
            ->setHeaders($headers ? array_merge($headers, $defaultHeaders) : $defaultHeaders);
        return $client;
    }
    
    protected function fetchServers()
    {
        $response = $this->getHttpClientWithHeaders(null, $this->rootServer)
            ->get('/v1/tfs.list');
        if ( $response->isOK() ) {
            $servers = explode("\n", trim($response->getContent()));
            if ( ctype_digit($servers[0]) ) {
                $this->maxAccessCount = array_shift($servers);
                $this->servers = $servers;
                $this->accessCount = 0;
            }
        } else {
            throw new \RuntimeException($response->getCode(), $response->getMessage());
        }
    }
    
    protected function getServer()
    {
        if ( !isset($this->servers) || $this->accessCount > $this->maxAccessCount ) {
            $this->fetchServers();
        }
        $this->accessCount++;
        return $this->servers[array_rand($this->servers)];
    }
}
