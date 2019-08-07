<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 29/07/2019
 * Time: 14:02
 */

namespace CronBundle\Service\UnFichier;


use CronBundle\Exception\UploaderException;
use Symfony\Component\Filesystem\Filesystem;

class DownloadService extends UnFichier
{

    private $key_api;


    private $curl;
    private $root_dir;

    private $filesystem;
    private $tmp_dir;
    private $tmp_path;
    private $tmp_file;
    private $tmp_header;



    private $ext;

    private $path;

    public function __construct(string $tmp_dir, string $root_dir, string $key_api)
    {
        parent::__construct();

        $this->tmp_dir = $tmp_dir;
        $this->root_dir = $root_dir;
        $this->key_api = $key_api;

        $this->filesystem = new Filesystem();
    }

    public function download(string $url)
    {
        return $this->fileTemp($url);
    }

    private function fileTemp(string $url)
    {
        $this->tmp_dir = $this->tmp_dir . uniqid();
        $this->tmp_path = $this->tmp_dir . DIRECTORY_SEPARATOR . uniqid();

        $this->filesystem->mkdir($this->tmp_dir, 0777);

        if(!$this->tmp_file = fopen($this->tmp_path, 'w+'))
            throw new UploaderException('UnFichier download >>>> Pas réussi à crééLe le fichier temporaire pour la video');

        if(!$this->tmp_header = tmpfile())
            throw new UploaderException('UnFichier download >>>> Pas réussi à créé Le le fichier temporaire pour la header');

        return $this->getLinkDownload($url);
    }


    private function getLinkDownload(string $url)
    {
        $this->curl = curl_init();
        curl_setopt_array($this->curl,[
            CURLOPT_HTTPHEADER => ['Content-Type: application/json' , 'Authorization: Bearer ' . $this->key_api],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode(["url" => $url]),
            CURLOPT_URL => "https://api.1fichier.com/v1/download/get_token.cgi"
        ]);

        $curlResponse = curl_exec($this->curl);

        if($err = curl_error($this->curl))
            throw new UploaderException("curl error ==> {$err}");

        curl_close($this->curl);

        if(!$result = json_decode($curlResponse))
            throw new UploaderException('UnFichier getLinkDownload >>>> Pas réussiAdecco des logis sans de réponse de 1fichier');

        if($result->status != 'OK')
            throw new UploaderException('link');

        return $this->downloadFile($result->url);
    }



    private function downloadFile(string $url)
    {

        $this->curl = curl_init();

        curl_setopt_array($this->curl,[
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_WRITEHEADER => $this->tmp_header,
            CURLOPT_FILE => $this->tmp_file,
            CURLOPT_URL => $url
        ]);

        curl_exec($this->curl);

        if($err = curl_error($this->curl))
            throw new UploaderException("curl error ==> {$err}");

        curl_close($this->curl);


        return $this->videoProcessing();

    }


    /**
     * @throws UploaderException
     */
    private function videoProcessing(){

        rewind($this->tmp_header);
        $headers = stream_get_contents($this->tmp_header);

        if(!preg_match("/.*filename=['\"]([^'\"]+)|.*filename=([^ ]+);/", $headers, $matches))
            throw new UploaderException('UnFichier Download >>>> not have name file');

        if(!$this->ext = pathinfo($matches[1], PATHINFO_EXTENSION))
            throw new UploaderException('UnFichier Download >>>> Ext not recover');

        if(!$this->ext = preg_replace("/[^A-Za-z0-9]/", '', $this->ext))
            throw new UploaderException('UnFichier Download >>>> error clear Alph Num');

       // $this->filesystem->rename($this->tmp_path, $this->tmp_path . '.' . $ext);

      //  $this->tmp_path .= ".{$ext}";

        //$this->filesystem->chmod($this->tmp_path, 0755);

        return $this;
    }

    public function save($path)
    {
        $this->path = $path .'.' . $this->ext;
        $this->filesystem->mkdir(dirname($this->path), 0777);
        $this->filesystem->rename($this->tmp_path, $this->path);
        $this->filesystem->remove([$this->tmp_dir]);
        return $this;
    }


    public function getPath()
    {
        if(empty($this->path))
            throw new UploaderException('UnFichier getPath >>>> error path empty');

        return $this->path;
    }



    public function __destruct() {
        if (is_resource($this->curl)) curl_close($this->curl);
        if (is_resource($this->tmp_header)) fclose($this->tmp_header);
        if(is_dir($this->tmp_dir)) $this->filesystem->remove([$this->tmp_dir]);
        return $this;
    }



}