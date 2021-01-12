<?php
    class GoogleChart{
        var $width,$height;
        var $chart_type;
        var $api_url;
        var $parameters;
        function GoogleChart($w,$h,$type,$url="http://chart.apis.google.com/chart"){
            $this->parameters = array();
            $this->width = $w; 
            $this->height = $h; 
            $this->chart_type = $type;
            $this->api_url = $url;
            $this->parameters['chs'] = $w."x".$h;
            $this->parameters['cht'] = $type;
            
        }   


        function draw(){
            $url = $this->api_url;
            $params = array();
            foreach($this->parameters as $key => $value){
                $params[] = "$key=$value";
            }   
            $url .= "?".implode("&",$params);
            header("Location: $url");
        }   
    } //class GoogleChart

    class GooglePieChart extends GoogleChart{
        function GooglePieChart($w,$h,$type="p"){
            parent::GoogleChart($w,$h,$type);
        }   

        function setData($data,$labels=null,$legends=null){
            $this->parameters['chd'] = "t:".implode(",",$data);
            if($labels) $this->setLabels($labels);
            if($legends) $this->setLegends($legends);
        }   

        function setLabels($labels){
            $this->parameters['chl'] = implode("|",$labels);
        }

        function setLegends($legends){
            $this->parameters['chdl'] = implode("|",$legends);
        }
    } //class GooglePieChart extends: GoogleChart

    class GooglePieChart3D extends GooglePieChart{
        function GooglePieChart3D($w,$h){
            parent::GooglePieChart($w,$h,"p3");
        }
    }