class GdTableServer
{

     public $config = [
        'border' => 0,//图片外边框
        'title_height' => 40,//报表名称高度
        'title_font_size' => 18,//报表名称字体大小
        'font_ulr' =>'',//字体文件路径
        'text_size' => 14,//正文字体大小
        'row_hight' => 30,//每行数据行高
        'field_width'=>200, //合作默认宽度
        'table_explain_height' => 30, //汇总行高
        'table_explain_font_size' => 16, //汇总字体
        'file_name' => '', //文件名
    ];
    /**
     * 设置配置
     */
    public function setConfig($config){
        
        foreach($config as $key=>$v){
           $this->config[$key] = $v;
        }
        return true;
    }

    public function getConfig(){
       return  $this->config;
    }

    

    //  $params = [
    //         'title' => "哈哈", //表头名称
    //         'data' => [[  //哈哈
    //             'name' => '啊实打实地方',
    //             'age' => '1',
    //             '哈哈' => '阿萨是的',
    //             '折扣率' => '0.0',
    //             '2' => '阿斯蒂芬',
    //         ]],
    //         'left_table_explain'	=>[  //图片左上角汇总说明数据，可为空
    //             '產品:100',
    //         ],
    //         'rigth_table_explain'	=>[  //图片右上角汇总说明数据，可为空
    //             '產品:100',
    //         ],
    //         'table_header'	=> [  //表头信息
    //                 0   =>  '產品',
    //                 1   =>  '表头1',
    //                 2   =>  '表头2',
    //                 3   =>  '表头3',
    //                 4   =>  '表头4',
    //             ],

    //         'field_width'	=> [   //每个格子的宽度，可根据数据长度自定义
    //                 0   =>  '260',
    //                 1   =>  '200',
    //                 2   =>  '200',
    //                 3   =>  '200',
    //                 4   =>  '220',
    //             ],
    //     ];
    
   /**
     * 生成表格
     */
    public function create_table($params)
    {
        // 初始化数量
        if(!isset($params['title']) || empty($params['title'])){
            $this->config['title_height'] = 0;  
        }
        // 初始化左右边表头汇总的高度
        $left_table_explain = 0;
        $rigth_table_explain = 0;
        // 计算汇总的高度
    	//如果表说明部分不为空，则增加表图片的高度
        if(isset($params['left_table_explain']) && !empty($params['left_table_explain'])){
            $left_table_explain = ($this->config['table_explain_height'] * count($params['left_table_explain']));
        }

        if(isset($params['rigth_table_explain']) && !empty($params['rigth_table_explain'])){
            $rigth_table_explain = ($this->config['table_explain_height'] * count($params['rigth_table_explain']));
        }
        $table_explain = $left_table_explain;
        if($rigth_table_explain > $left_table_explain){
            $table_explain = $rigth_table_explain;
        }

        $this->config['title_height'] += $table_explain;

        //计算图片总宽
        $w_sum = $this->config['border'];
        foreach($params['table_header'] as $key => $value){

           $this->config['column_x_arr'][$key] = $w_sum;
            // 取指定单元格宽度    
            if(isset($params['field_width'][$key])){
                $w_sum += $params['field_width'][$key];

            }else{
                //取默认单元格宽度
                $w_sum += $this->config['field_width'];
                $params['field_width'][$key] = $this->config['field_width'];
            }
        }
        $row_hight=[];
        $tab_hight = 0;
        //计算字体总高度
        foreach ($params['data'] as $key => $item) {
            $max = $this->config['row_hight'];
            $k = 0;
            foreach($item as $v){
                $td_box = imagettfbbox($this->config['title_font_size'], 0, $this->config['font_ulr'], $v);//
                $title_fout_width = $td_box[2] - $td_box[0];//右下角 X 位置 - 左下角 X 位置 为文字宽度
                $td_height = ceil($title_fout_width/$params['field_width'][$k])*($this->config['row_hight']+$this->config['text_size']/2);

                if($max<$td_height){
                    $max = $td_height;
                }
                $k++;
            }
            $row_hight[$key] = $max;
            $tab_hight += $max;
        }

        $this->config['img_width'] = $w_sum + $this->config['border'] * 2-$this->config['border'];//图片宽度
        $this->config['img_height'] =    $this->config['row_hight']+$tab_hight+ $this->config['border'] * 2 + $this->config['title_height'];//图片高度
        $border_top = $this->config['border'] + $this->config['title_height'];//表格顶部高度
        $border_bottom = $this->config['img_height'] - $this->config['border'];//表格底部高度
        $img = imagecreatetruecolor($this->config['img_width'], $this->config['img_height']);//创建指定尺寸图片

        $bg_color = imagecolorallocate($img, 255,255,255);//设定图片背景色
        $text_coler = imagecolorallocate($img, 0, 0, 0);//设定文字颜色
        $border_coler = imagecolorallocate($img, 0, 0, 0);//设定边框颜色

        imagefill($img, 0, 0, $bg_color);//填充图片背景色

        //先填充一个黑色的大块背景
        imagefilledrectangle($img, $this->config['border'], $this->config['border'] + $this->config['title_height'], $this->config['img_width'] - $this->config['border'], $this->config['img_height'] - $this->config['border'], $border_coler);//画矩形

        //再填充一个小两个像素的 背景色区域，形成一个两个像素的外边框
        imagefilledrectangle($img, $this->config['border'] + 2, $this->config['border'] + $this->config['title_height'] + 2, $this->config['img_width'] - $this->config['border'] - 2, $this->config['img_height'] - $this->config['border'] - 2, $bg_color);//画矩形
        //画表格纵线 及 写入表头文字
        $sum = $this->config['border'];
        foreach($this->config['column_x_arr'] as $key => $x){
            imageline($img, $x, $border_top, $x, $border_bottom,$border_coler);//画纵线
            $this_title_box = imagettfbbox($this->config['text_size'], 0, $this->config['font_ulr'], $params['table_header'][$key]);
            $title_x_len = $this_title_box[2] - $this_title_box[0];
            imagettftext($img, $this->config['text_size'], 0, $sum + (( $params['field_width'][$key])/2 - ($title_x_len/2)), $border_top + ($this->config['row_hight']+$this->config['text_size'])/2, $text_coler, $this->config['font_ulr'], $params['table_header'][$key]);//写入表头文字
            $sum += $params['field_width'][$key];
        }

        //画表格横线
        foreach($params['data'] as $key => $item){
            if($key !=0 ){
                $border_top += $row_hight[$key-1];
            }else{
                $border_top += $this->config['row_hight'];
            }

            //画横线
            imageline($img, $this->config['border'], $border_top, $this->config['img_width'] - $this->config['border'], $border_top, $border_coler);
            $sub = 0;
            $sum = $this->config['border'];
            foreach ($item as $value){
                if(!isset($params['field_width'][$sub])){
                    $params['field_width'][$sub] = $this->config['field_width'];
                }

                $this_title_box = imagettfbbox($this->config['text_size'], 0, $this->config['font_ulr'], $value);
                $title_x_len = $this_title_box[2] - $this_title_box[0];
                $td_height = ceil($title_x_len/$params['field_width'][$sub]);
                $start = 0;
                $start_top = $border_top + ($this->config['row_hight']+$this->config['text_size'])/2;
                // 计算文字的高度实现表格自动换行
                for ($i=1; $i <= $td_height; $i++) { 
                    if($td_height ==  $i){
                        $text = mb_substr($value,$start,strlen($value)-$start);
                    }else{
                        $leng =  (int)(mb_strlen($value)/$td_height);
                        $text = mb_substr($value,$start,$leng);
                        $start += $leng;
                    }
                    $this_text_box = imagettfbbox($this->config['text_size'], 0, $this->config['font_ulr'], $text);
                    $text_x_len = $this_text_box[2] - $this_text_box[0];
                       $width = $sum + (($params['field_width'][$sub])/2 - $text_x_len/2);
                     imagettftext($img, $this->config['text_size'], 0, $width, $start_top, $text_coler, $this->config['font_ulr'], $text);//写入data数据
                     $start_top += ($this->config['row_hight']+($this->config['text_size']/2));
                }
                $sum += $params['field_width'][$sub];
                $sub++;
            }
        }


        // 验证是否需要填写标题
        if(isset($params['title']) && !empty($params['title'])){
                //计算标题写入起始位置
            $title_fout_box = imagettfbbox($this->config['title_font_size'], 0, $this->config['font_ulr'], $params['title']);//imagettfbbox() 返回一个含有 8 个单元的数组表示了文本外框的四个角：
            $title_fout_width = $title_fout_box[2] - $title_fout_box[0];//右下角 X 位置 - 左下角 X 位置 为文字宽度
            $title_fout_height = $title_fout_box[1] - $title_fout_box[7];//左下角 Y 位置- 左上角 Y 位置 为文字高度
            imagettftext($img, $this->config['title_font_size'], 0, ($this->config['img_width'] - $title_fout_width)/2, 30, $text_coler, $this->config['font_ulr'], $params['title']);

        }
        //居中写入标题
        //设置图片左上角信息
        if(isset($params['left_table_explain']) && !empty($params['left_table_explain'])){
            $a_hight = 0;
            foreach ($params['left_table_explain'] as $key => $value) {
                // 计算起始位置
                $a_hight += $this->config['table_explain_height'];
                imagettftext($img, $this->config['table_explain_font_size'], 0, 10, $this->config['table_explain_height']+$a_hight, $text_coler, $this->config['font_ulr'], $value);
                
            }
        }

  
        if(isset($params['rigth_table_explain']) && !empty($params['rigth_table_explain'])){
            $rigth_x = 10;
            // 计算起始位置
            foreach ($params['rigth_table_explain'] as $key => $value) {
                // 计算起始位置
                $title_fout_box = imagettfbbox($this->config['title_font_size'], 0, $this->config['font_ulr'], $value);//
                //计算实际宽度
                $title_fout_width = $title_fout_box[2] - $title_fout_box[0];//右下角 X 位置 - 左下角 X 位置 为文字宽度
                // 获得最大宽度
                if($title_fout_width>$rigth_x){
                    $rigth_x = $title_fout_width;
                }
            }
            // 计算起始位置
            $rigth_start_x = $this->config['img_width']-$rigth_x;
            // 宽度不够
            if($rigth_start_x<$rigth_x){
                return false;
            }
            $rigth_hight = 0;
            foreach ($params['rigth_table_explain'] as $key => $value) {
                // 计算起始位置
                $rigth_hight += $this->config['table_explain_height'];
                imagettftext($img, $this->config['table_explain_font_size'], 0, $rigth_start_x, $this->config['table_explain_height']+$rigth_hight, $text_coler, $this->config['font_ulr'], $value);
                
            }
        }

        if(empty($this->config['file_name'])){
            $this->config['file_name'] = time().'.png';
        }
    	$save_path = $this->config['file_path'] . $this->config['file_name'];
        // 创建文件夹
        if(!is_dir($this->config['file_path']))//判断存储路径是否存在，不存在则创建
        {
            mkdir($this->config['file_path'],0777,true);
        }
        imagepng($img,$save_path);//输出图片，输出png使用imagepng方法，输出gif使用imagegif方法
          // 释放资源
        imagedestroy($img);
        return $save_path;
    }
