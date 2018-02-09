
<?php 
 /*
 * @Author: 刘根每 
 * @Date: 2018-02-08 14:10:32 
 * @Last Modified by: 刘根每
 * @Last Modified time: 2018-02-09 19:22:18
 */


    /*
    * 遍历所有php文件内容，根据json存储的文件名进行替换，每次打包都执行一次，达到更迭版本号的目的
    */ 

    class Format{ 
        public function getFilesName( $path, $type , &$files = array()) {//获取到$path路径下的所有$type类型的文件名，返回arr
            if ( !is_dir( $path ) ) return null;
            $handle = opendir( $path );
            while ( false !== ( $file = readdir( $handle ) ) ) {
                if ( $file != '.' && $file != '..' ) {
                    $path2 = $path . '/' . $file;
                    if ( is_dir( $path2 ) ) {
                        $this->getFilesName( $path2, $type , $files );
                    } else {
                        if ( preg_match( "/\.($type)$/i" , $file ) ) {
                            $files[] = $path2;
                        }
                    }
                }
            }
            return $files;
        }

        public function read_static_cache($filename, $url){//读取json数据
            $json_string = file_get_contents($_SERVER['DOCUMENT_ROOT']."/php/tempSource/temp.json");  
            $data = json_decode($json_string, true);  
            if ($filename == '' && $url == '') {
                return $data;
            }else{
                return $data[$filename][$url];
            }
        }  

        public function write_static_cache($dir){//获取最新的css、js文件修改时间写入json等待调用
            //读取json文件内容
            // echo str_replace('.css', '' ,'resource/css/parkinfo.css1518013531.css');exit;
            // echo preg_replace('|[0-9]+|','',str_replace('.css', '' ,'resource/css/parkinfo.css1518013531.css'));exit;
            $json_string = file_get_contents($_SERVER['DOCUMENT_ROOT']."/php/tempSource/temp.json");  
            $data = json_decode($json_string, true);  
            //读取所有css文件修改时间戳
            $files_css = $this->getFilesName($_SERVER['DOCUMENT_ROOT'].'\\'.$dir,'css');
            for ($i=0; $i < count($files_css); $i++) { 
                $file_css_path = $files_css[$i];
                $file_css_old = 'resource/css/'.basename($file_css_path);
                $file_css_new = preg_replace('|[0-9]+|','',str_replace('.css', '' ,$file_css_old)).filemtime($files_css[$i]).'.css';
                $data[$file_css_old]['url'] = $file_css_new;
                
            }
            $files_css = $this->getFilesName($_SERVER['DOCUMENT_ROOT'].'\\'.$dir,'js');
            for ($i=0; $i < count($files_css); $i++) { 
                $file_css_path = $files_css[$i];
                $file_css_old = 'resource/js/'.basename($file_css_path);
                $file_css_new = preg_replace('|[0-9]+|','',str_replace('.js', '' ,$file_css_old)).filemtime($files_css[$i]).'.js';
                $data[$file_css_old]['url'] = $file_css_new;
                
            }
            //修改完所有js、css文件名存入json中等待调用
            $json_string = json_encode($data);  
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/php/tempSource/temp.json", $json_string); 
        }  

        public function changeFilesContent($dir) {//遍历所有php文件内容，从json中取出键名进去匹配修改为该键的值，同时把对应的css、js文件名改掉
            //css
            $files = $this->getFilesName($_SERVER['DOCUMENT_ROOT'].'\\'.$dir,'php');
            $json_data = $this->read_static_cache('','');//获取所有json数据
            foreach ($json_data as $key => $value) {
                for ($i=0; $i < count($files); $i++) { 
                    $file_path = $files[$i];
                    if(file_exists($file_path)){
                        $str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
                        if (strpos($str,$key)) {
                            $file_new = $this->read_static_cache($key,'url');//获取到json中的新路径
                            if ($file_new) {
                                $oldpath = $_SERVER['DOCUMENT_ROOT'].'\\'.$dir.'\\'.$key;//原路径
                                $newpath = $_SERVER['DOCUMENT_ROOT'].'\\'.$dir.'\\'.$file_new;//新路径
                                if(rename($oldpath , $newpath)){//修改成功路径
                                    echo '文件名修改成功' ;
                                }else{
                                    echo '文件名修改失败';
                                };
                            }
                        }else{
                            continue;
                        }
                    }
                }
                
            }


            foreach ($json_data as $key => $value) {
                for ($i=0; $i < count($files); $i++) { 
                    $file_path = $files[$i];
                    if(file_exists($file_path)){
                        $str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
                        if (strpos($str,$key)) {
                            $file_new = $this->read_static_cache($key,'url');//获取到json中的新路径
                            if ($file_new) {
                                $str_new = str_replace($key, $file_new ,$str);//替换php文件中对应的路径
                                file_put_contents($file_path, $str_new);//将新内容写入
                                echo 'php文件路径修改成功' ;
                            }else{
                                echo 'php文件路径修改失败';
                            }
                        }else{
                            continue;
                        }
                    }
                }
                
            }
        }
    }
