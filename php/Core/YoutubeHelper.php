<?php


class YoutubeHelper {   

    protected $link = 'https://www.googleapis.com/youtube/v3/';
    protected $apk;

    public function __construct() {
        //ENV from Dotenv
        $this->apk = $_ENV['YOUTUBE_API_KEY'];
    }

    protected $listPattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.?be)\/(?:playlist\?list=|watch\?v=[^&]*&list=)([a-zA-Z0-9_-]+)/i';
    protected $videoPattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i';
    
    public function searchVideo ($link){
        $videoId = $listId = null;

        if (preg_match($this->listPattern, $link, $match)) {
            $listId = $match[1];            
        }

        if (preg_match($this->videoPattern, $link, $match)) {
            $videoId = $match[1];            
        }

        return [$videoId, $listId];
    }
    
    public function getVideoInfo($video_id){
        $result = [];
        $url = $this->link."videos?id={$video_id}&key={$this->apk}&part=snippet,statistics,contentDetails";
        //https://www.googleapis.com/youtube/v3/videos?id=lYpAFVCH6W0&key=AIzaSyCqFQEqB9ewlPyoQw6mTLsKPhyDl-soh80&part=snippet,statistics,contentDetails

        $data = @json_decode(@file_get_contents($url), true);
        
        $info = $data['items'][0];
        
        $result["id"] = $video_id;
        $result["title"] = $info['snippet']['title'];
        $result["description"] = $info['snippet']['description'];
        $result["creator"] = $info['snippet']['channelTitle'];
        $result["date_create"] = $info['snippet']['publishedAt'];
        
        $result["view_count"] = $info['statistics']['viewCount'];
        $result["like_count"] = $info['statistics']['likeCount'];

        $dur = $info['contentDetails']['duration'];
        #$date->add(new DateInterval($dur));
        $interval = new DateInterval($dur);
        $result["duration"] = $interval->format('%H') * 3600 + $interval->format('%I') * 60 + $interval->format('%S');
        

        return $result;
        
    }
    
    public function getListInfo($playlistId){
        $result = [];
        $urlList = $this->link."playlists?id={$playlistId}&key={$this->apk}&part=snippet,contentDetails";
        $listData = @json_decode(@file_get_contents($urlList), true);        
        $info = $listData['items'][0];
        
        $result["title"] = $info['snippet']['title'];
        $result["description"] = $info['snippet']['description'];
        $result["creator"] = $info['snippet']['channelTitle'];
        $result["date_create"] = $info['snippet']['publishedAt'];
        $result["count"] = $info['contentDetails']['itemCount'];        

        /*
        $urlListItems = $this->link."playlistItems?part=snippet&playlistId={$playlistId}&maxResults=50&key={$this->apk}";
        //pageToken = nextPageToken 
        $itemsData = @json_decode(@file_get_contents($urlListItems), true);
        foreach ($itemsData['items'] as $id => $iteminfo){
            $video_id = $iteminfo['snippet']['resourceId']['videoId'];
            $result["items"][] = $this->getVideoInfo($video_id);
        }
        */
        

        return $result;
        
    }

    public function getListItems ($playlistId)
    {
        $result = [];        
        $urlListItems = $this->link."playlistItems?playlistId={$playlistId}&maxResults=50&key={$this->apk}&part=snippet";        
        $itemsData = @json_decode(@file_get_contents($urlListItems), true);

        foreach ($itemsData['items'] as $id => $iteminfo){
            $video_id = $iteminfo['snippet']['resourceId']['videoId'];
            $result[] = $this->getVideoInfo($video_id);
        }
        return $result;        
    }
    /*
    public function getListInfo($playlistId){
        $result = [];
        $urlList = $this->link."playlists?part=snippet&id={$playlistId}&key={$this->apk}";
        $urlListItems = $this->link."playlistItems?part=snippet&playlistId={$playlistId}&maxResults=50&key={$this->apk}";        

        $listData = @json_decode(@file_get_contents($urlList), true);
        //pageToken = nextPageToken        
        
        $info = $listData['items'][0];
        
        $result["title"] = $info['snippet']['title'];
        $result["description"] = $info['snippet']['description'];
        $result["creator"] = $info['snippet']['channelTitle'];
        $result["date_create"] = $info['snippet']['publishedAt'];        

        
        $itemsData = @json_decode(@file_get_contents($urlListItems), true);
        foreach ($itemsData['items'] as $id => $iteminfo){
            $video_id = $iteminfo['snippet']['resourceId']['videoId'];
            $result["items"][] = $this->getVideoInfo($video_id);
        }
        

        return $result;
        
    }
    */


}

