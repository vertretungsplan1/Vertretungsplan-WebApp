<?php 
    //ini_set('display_errors','1');
    require_once __DIR__.'/../dataFetcher/settings.php';
    require_once __DIR__.'/../dataFetcher/info.php';
    $subst_type="";
    $url=getSetting('plan_url')==null ? "" : getSetting('plan_url');
    $asm=getSetting('asm');
    $asms=getInfo('asms');
    for($a=0;$a<count($asms);++$a){
        if(!isset($asm[$asms[$a]['kname']]))
        $asm[$asms[$a]['kname']]=($asms[$a]['multipurpose'] ? [] : "false");
    }
    $colors=getInfo('default_colors');
    $typs=json_decode('[{
		"name": "Vertretung",
		"color": 1
	}, {
		"name": "Statt-Vertretung",
		"color": 1
	}, {
		"name": "Lehrertausch",
		"color": 9
	}, {
		"name": "Exkursion",
		"color": 0
	}, {
		"name": "eigenverantwortliches Arbeiten",
		"color": 4
	}, {
		"name": "Tausch",
		"color": 9
	}, {
		"name": "Verlegung",
		"color": 9
	}, {
		"name": "Unterricht ge\u00e4ndert",
		"color": 0
	}, {
		"name": "Veranst.",
		"color": 0
	}, {
		"name": "Sondereins.",
		"color": 0
	}, {
		"name": "Entfall",
		"color": 2
	}, {
		"name": "Vormerkung",
		"color": 0
	}, {
		"name": "Raum\u00e4nderung",
		"color": 3
	}, {
		"name": "Teil-Vtr.",
		"color": 1
	}, {
		"name": "Bereitschaft",
		"color": 4
	}, {
		"name": "Exkursion, Veranstaltung",
		"color": 4
	}]',true);
    $teacher_plan=false;
    $igr=$_GET['igr']==="1";
    if(!isset($subst_type)||$subst_type===""){
        $subst_type="FIRST";
    }
    $ch=curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html=trim(curl_exec($ch));
    $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
    $dom=new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $xpath=new DOMXPath($dom);
    $frames=[];
    $frames[0]['src']=curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    $frames[0]['untistype']=false;
    $meta=$xpath->query('//meta[@name="GENERATOR"] | //meta[@name="generator"] | //meta[@name="Generator"]');
    if(count($meta)>0){
        $meta=$meta[0];
        if(stripos(" ".$meta->getAttribute("content"),"gp-Untis"))
            $frames[0]['untistype']="Untis Alpha";
        else if(stripos(" ".$meta->getAttribute("content"),"Untis")){
            $frames[0]['untistype']="Untis Beta";
            $frames[0]['untisbetatype']="subst";
            if(count($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_title ")]'))>0)
                $frames[0]['untisbetatype']="mon";
        }
    }
    $frames=array_merge($frames,findAllFrames($ch,$dom));
    $uts=array_column($frames,"untistype");
    $ret=[];
    if(in_array("Untis Alpha",$uts)){
        $ualpha=[];
        for($a=0;$a<count($frames);++$a){
            if($frames[$a]['untistype']==="Untis Alpha"){
                $expath=explode("/",parse_url($frames[$a]['src'],PHP_URL_PATH));
                if(in_array("navbar.htm",$expath))
                    $ualpha['navbar']=$frames[$a];
                else if(in_array("welcome.htm",$expath))
                    $ualpha['welcome']=$frames[$a];
                else if(in_array("title.htm",$expath))
                    $ualpha['title']=$frames[$a];
                else if(in_array("default.htm",$expath))
                    $ualpha['default']=$frames[$a];
                else
                    $ualpha['unknown'][count($ualpha['unknown'])]=$frames[$a];
            }
        }
        $untisbase="";
        if(isset($ualpha['welcome']))
            $untisbase=dirname($ualpha['welcome']['src']);
        else if(isset($ualpha['default']))
            $untisbase=dirname($ualpha['default']['src']);
        else if(isset($ualpha['navbar']))
            $untisbase=dirname(dirname($ualpha['navbar']['src']));
        else if(isset($ualpha['title']))
            $untisbase=dirname(dirname($ualpha['title']['src']));
        if($untisbase===""){
            $ret['success']=false;
            $ret['errcode']="VPL_NOT_DETECTED";
            $ret['error']="Vertretungsplan konnte nicht erkannt werden";
        }
        else{
            $ch=curl_init($untisbase.'/frames/title.htm');
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $html=curl_exec($ch);
            if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                $dom=new DOMDocument();
                $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                $xpath=new DOMXPath($dom);
                $name=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " schoolname ")]');
                if(count($name)>0){
                        $name=$name[0];
                    $iname=trim(DOMinnerHTML($name));
                    if(strpos(" ".$iname,"<img")){
                        $ret['name']=trim(substr($iname,0,strpos(" ".$iname,"<img")-1));
                    }
                    else
                        $ret['name']=trim($iname);
                }
                else{
                    $ret['name']="";
                }
                $desc=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " description ")]');
                if(count($desc)>0){
                    $desc=$desc[0];
                    $idesc=trim(DOMinnerHTML($desc));
                    if(strpos(" ".$idesc,"Stand: "))
                        $ret['lachpl']=date("d.m.Y, H:i",strtotime(substr($idesc,strpos(" ".$idesc,"Stand: ")+6)));
                }
            }
            else{
                $ret['name']="VAP";
            }
            $ch=curl_init($untisbase.'/frames/navbar.htm');
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $html=curl_exec($ch);
            if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                $dom=new DOMDocument();
                $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                $xpath=new DOMXPath($dom);
                if($ret['name']===""){
                    $name=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " schoolname ")]');
                    if(count($name)>0){
                            $name=$name[0];
                        $iname=trim(DOMinnerHTML($name));
                        if(strpos(" ".$iname,"<img")){
                            $ret['name']=trim(substr($iname,0,strpos(" ".$iname,"<img")-1));
                        }
                        else
                            $ret['name']=trim($iname);
                    }
                    else{
                        $ret['name']="VAP";
                    }
                }
                if(!isset($ret['lachpl'])){
                    $desc=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " description ")]');
                    if(count($desc)>0){
                        $desc=$desc[0];
                        $idesc=trim(DOMinnerHTML($desc));
                        if(strpos(" ".$idesc,"Stand: "))
                            $ret['lachpl']=date("d.m.Y, H:i",strtotime(substr($idesc,strpos(" ".$idesc,"Stand: ")+6)));
                    }
                }
                $weeks=iterator_to_array($xpath->query('//select[@name="week"]/option/@value'));
                $wks=array_column($weeks,"value");
                if(count($wks)>0){
                    $types=iterator_to_array($xpath->query('//select[@name="type"]/option/@value'));
                    $tps=array_column($types,"value");
                    $tpp=0;
                    if($subst_type!=="FIRST"){
                        if(in_array($subst_type,$tps))
                            $tpp=array_search($subst_type,$tps);
                    }
                    if(count($tps)>0){
                        $pos=strpos(" ".$html,'case "'.$tps[$tpp].'": PopulateElementOption(Form,')-1+strlen('case "'.$tps[$tpp].'": PopulateElementOption(Form,');
                        $case=substr($html,$pos);
                        $pos=strpos(" ".$case,');')-1;
                        $case=substr($case,0,$pos);
                        $expl=explode(", ",$case);
                        $pos=strpos(" ".$html,'var '.trim($expl[0]).' = [')-1+strlen('var '.trim($expl[0]).' = ');
                        $array=substr($html,$pos);
                        $pos=strpos(" ".$array,'];');
                        $array=json_decode(substr($array,0,$pos),true);
                        $ret[trim($expl[0])]=$array;
                        $start=1;
                        $end=count($array);
                        if(trim($expl[1])!=="0"&&trim($expl[1])!=="2"){
                            if(strpos(" ".$html,"var ".trim($expl[1])." = ")){
                                $var=substr($html,strpos(" ".$html,"var ".trim($expl[1])." = ")-1+strlen("var ".trim($expl[1])." = "));
                                $pos=strpos(" ".$var,";")-1;
                                $var=substr($var,0,$pos);
                                if(trim($var)!=="0"&&trim($var)!=="2"){
                                    $start=0;
                                    $end=0;
                                }
                            }
                            else{
                                $start=0;
                                $end=0;
                            }
                        }
                        $ret['plan']=[];
                        for($a=0;$a<count($wks);++$a){
                            for($b=$start;$b<=$end;++$b){
                                
                                $file=$b;
                                while(strlen($file)<5){
                                    $file="0".$file;
                                }
                                $ret['plan']=array_merge($ret['plan'],parsePlan($untisbase."/".$tps[$tpp]."/".$wks[$a]."/".$tps[$tpp].$file.".htm"),parsePlan($untisbase."/".$wks[$a]."/".$tps[$tpp]."/".$tps[$tpp].$file.".htm"));
                            }
                        }
                        $ret['plan']=concatenateAll($ret['plan']);
                    }
                    else{
                        $ret['success']=false;
                        $ret['errcode']="VPL_INVALID_WEEKS";
                        $ret['error']="Vertretungsplan konnte keine Wochen erkennen";
                    }
                }
                else{
                    $ret['success']=false;
                    $ret['errcode']="VPL_INVALID_WEEKS";
                    $ret['error']="Vertretungsplan konnte keine Wochen erkennen";
                }
            }
            else{
                $ret['success']=false;
                $ret['errcode']="VPL_NOT_DETECTED";
                $ret['error']="Vertretungsplan konnte nicht erkannt werden";
            }
        }
    }
    else if(in_array("Untis Beta",$uts)){
        $ubeta=[];
        for($a=0;$a<count($frames);++$a){
            if($frames[$a]['untistype']==="Untis Beta"){
                $ubeta[count($ubeta)]=$frames[$a];
            }
        }
        $appruntisbase="";
        $untisbase="";
        $btype="";
        $bpoint=$ubeta[0]['src'];
        if($ubeta[0]['untisbetatype']==="mon"){
            $appruntisbase=dirname(dirname($ubeta[0]['src']));
            $btype="mon";
        }
        else if($ubeta[0]['untisbetatype']==="subst"){
            $appruntisbase=dirname($ubeta[0]['src'])."/../../";
            $btype="subst";
        }
        $ch=curl_init($ubeta[0]['src']);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $html=curl_exec($ch);
        $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
        $dom=new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
        $xpath=new DOMXPath($dom);
        if($btype==="mon"){
            $ch=curl_init($appruntisbase.'/subst_001.htm');
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($ch);
            if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                $untisbase=$appruntisbase;
            }
            else{
                $ch=curl_init($appruntisbase.'/index.htm');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_exec($ch);
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                    $untisbase=$appruntisbase;
                } 
            }
        }
        else if($btype==="subst"){
            $ch=curl_init($appruntisbase.'/welcome.htm');
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($ch);
            if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                $ch=curl_init($appruntisbase.'/default.htm');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_exec($ch);
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                    $untisbase=dirname(curl_getinfo($ch,CURLINFO_EFFECTIVE_URL));
                }
            }
        }
        if($untisbase!==""){
            if($btype==="mon"){
                $ch=curl_init($untisbase.'/subst_title.htm');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $html=curl_exec($ch);
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                    $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                    $dom=new DOMDocument();
                    $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                    $xpath=new DOMXPath($dom);
                    $head=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_head ")]')[0];
                    $right=$xpath->query('tr/td[@align="right"]/p',$head);
                    if(count($right)>0){
                        $rname=trim(DOMinnerHTML($right[0]));
                        if(strpos(" ".$rname,"<span")){
                            $ret['name']=trim(substr($rname,0,strpos(" ".$rname,"<span")-1));
                        }
                        else
                            $ret['name']=trim($right);
                        if(strpos(" ".$rname,"Stand: "))
                            $ret['lachpl']=date("d.m.Y, H:i",strtotime(substr($rname,strpos(" ".$rname,"Stand: ")+6)));
                    }
                    else
                        $ret['name']="VAP";
                }
                else
                    $ret['name']="VAP";
                $ch=curl_init($untisbase.'/subst_001.htm');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $html=curl_exec($ch);
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)!=200){
                    $ch=curl_init($untisbase.'/index.htm');
                    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    $html=curl_exec($ch);
                }
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                    $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                    $dom=new DOMDocument();
                    $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                    $frms=findAllFrames($ch,$dom);
                    $ubeta=[];
                    for($a=0;$a<count($frms);++$a){
                        if($frms[$a]['untistype']==="Untis Beta"){
                            $ubeta[count($ubeta)]=$frms[$a];
                            
                        }
                    }
                    $pages=[];
                    for($a=0;$a<count($ubeta);++$a){
                        $pages[count($pages)]=$ubeta[$a]['src'];
                        $ch=curl_init($ubeta[$a]['src']);
                        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        $html=curl_exec($ch);
                        $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                        $dom=new DOMDocument();
                        $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                        $xpath=new DOMXPath($dom);
                        $refr=$xpath->query('//meta[@http-equiv="Refresh"] | //meta[@http-equiv="refresh"] | //meta[@http-equiv="REFRESH"]');
                        if(count($refr)>0){
                            $refr=$refr[0];
                            $cnt=$refr->getAttribute("content");
                            if(stripos(" ".$cnt,"URL=")){
                                $newfile=trim(substr($cnt,stripos(" ".$cnt,"URL=")+3));
                                if(!in_array(dirname($pages[count($pages)-1]).'/'.$newfile,$pages)){
                                    $pages[count($pages)]=dirname($pages[count($pages)-1]).'/'.$newfile;
                                    $nfile=true;
                                    while($nfile){
                                        $ch=curl_init($pages[count($pages)-1]);
                                        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                        $html=curl_exec($ch);
                                        $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                                        if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                                            $dom=new DOMDocument();
                                            $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                                            $xpath=new DOMXPath($dom);
                                            $refr=$xpath->query('//meta[@http-equiv="Refresh"] | //meta[@http-equiv="refresh"] | //meta[@http-equiv="REFRESH"]');
                                            if(count($refr)>0){
                                                $refr=$refr[0];
                                                $cnt=$refr->getAttribute("content");
                                                if(stripos(" ".$cnt,"URL=")){
                                                    $newfile=trim(substr($cnt,stripos(" ".$cnt,"URL=")+3));
                                                    if(!in_array(dirname($pages[count($pages)-1]).'/'.$newfile,$pages)){
                                                        $pages[count($pages)]=dirname($pages[count($pages)-1]).'/'.$newfile;
                                                    }
                                                    else{
                                                        $nfile=false;
                                                    }
                                                }
                                                else{
                                                    $nfile=false;
                                                }
                                            }
                                            else{
                                                $nfile=false;
                                            }
                                        }
                                        else{
                                            $nfile=false;
                                            array_splice($pages,count($pages)-1,1);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $ret['plan']=[];
                    for($a=0;$a<count($pages);++$a){
                        $ret['plan']=array_merge($ret['plan'],parsePlan($pages[$a]));
                    }
                    $ret['plan']=concatenateAll($ret['plan']);
                }
            }
            else if($btype==="subst"){
                $ch=curl_init($untisbase.'/frames/title.htm');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $html=curl_exec($ch);
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                    $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                    $dom=new DOMDocument();
                    $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                    $xpath=new DOMXPath($dom);
                    $name=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " schoolname ")]');
                    if(count($name)>0){
                            $name=$name[0];
                        $iname=trim(DOMinnerHTML($name));
                        if(strpos(" ".$iname,"<img")){
                            $ret['name']=trim(substr($iname,0,strpos(" ".$iname,"<img")-1));
                        }
                        else
                            $ret['name']=trim($iname);
                    }
                    else{
                        $ret['name']="";
                    }
                    $desc=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " description ")]');
                    if(count($desc)>0){
                        $desc=$desc[0];
                        $idesc=trim(DOMinnerHTML($desc));
                        if(strpos(" ".$idesc,"Stand: "))
                            $ret['lachpl']=date("d.m.Y, H:i",strtotime(substr($idesc,strpos(" ".$idesc,"Stand: ")+6)));
                    }
                }
                else{
                    $ret['name']="VAP";
                }
                $ch=curl_init($untisbase.'/frames/navbar.htm');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $html=curl_exec($ch);
                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                    $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                    $dom=new DOMDocument();
                    $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                    $xpath=new DOMXPath($dom);
                    if($ret['name']===""){
                        $name=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " schoolname ")]');
                        if(count($name)>0){
                                $name=$name[0];
                            $iname=trim(DOMinnerHTML($name));
                            if(strpos(" ".$iname,"<img")){
                                $ret['name']=trim(substr($iname,0,strpos(" ".$iname,"<img")-1));
                            }
                            else
                                $ret['name']=trim($iname);
                        }
                        else{
                            $ret['name']="VAP";
                        }
                    }
                    if(!isset($ret['lachpl'])){
                        $desc=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " description ")]');
                        if(count($desc)>0){
                            $desc=$desc[0];
                            $idesc=trim(DOMinnerHTML($desc));
                            if(strpos(" ".$idesc,"Stand: "))
                                $ret['lachpl']=date("d.m.Y, H:i",strtotime(substr($idesc,strpos(" ".$idesc,"Stand: ")+6)));
                        }
                    }
                    $weeks=iterator_to_array($xpath->query('//select[@name="week"]/option/@value'));
                    $wks=array_column($weeks,"value");
                    if(count($wks)>0){
                        $types=iterator_to_array($xpath->query('//select[@name="type"]/option/@value'));
                        $tps=array_column($types,"value");
                        $tpp=0;
                        if($subst_type!=="FIRST"){
                            if(in_array($subst_type,$tps))
                                $tpp=array_search($subst_type,$tps);
                        }
                        if(count($tps)>0){
                            $pos=strpos(" ".$html,'case "'.$tps[$tpp].'": PopulateElementOption(Form,')-1+strlen('case "'.$tps[$tpp].'": PopulateElementOption(Form,');
                            $case=substr($html,$pos);
                            $pos=strpos(" ".$case,');')-1;
                            $case=substr($case,0,$pos);
                            $expl=explode(", ",$case);
                            $pos=strpos(" ".$html,'var '.trim($expl[0]).' = [')-1+strlen('var '.trim($expl[0]).' = ');
                            $array=substr($html,$pos);
                            $pos=strpos(" ".$array,'];');
                            $array=json_decode(substr($array,0,$pos),true);
                            $ret[trim($expl[0])]=$array;
                            $start=1;
                            $end=count($array);
                            if(trim($expl[1])!=="0"&&trim($expl[1])!=="2"){
                                if(strpos(" ".$html,"var ".trim($expl[1])." = ")){
                                    $var=substr($html,strpos(" ".$html,"var ".trim($expl[1])." = ")-1+strlen("var ".trim($expl[1])." = "));
                                    $pos=strpos(" ".$var,";")-1;
                                    $var=substr($var,0,$pos);
                                    if(trim($var)!=="0"&&trim($var)!=="2"){
                                        $start=0;
                                        $end=0;
                                    }
                                }
                                else{
                                    $start=0;
                                    $end=0;
                                }
                            }
                            $pages=[];
                            for($a=0;$a<count($wks);++$a){
                                for($b=$start;$b<=$end;++$b){
                                    $file=$b;
                                    while(strlen($file)<5){
                                        $file="0".$file;
                                    }
                                    $pages[count($pages)]=$untisbase."/".$tps[$tpp]."/".$wks[$a]."/".$tps[$tpp].$file.".htm";
                                    $pages[count($pages)]=$untisbase."/".$wks[$a]."/".$tps[$tpp]."/".$tps[$tpp].$file.".htm";
                                }
                            }
                            if(in_array($bpoint,$pages)){
                                $ret['plan']=[];
                                for($a=0;$a<count($pages);++$a){
                                        $ret['plan']=array_merge($ret['plan'],parsePlan($pages[$a]));
                                }
                            }
                            else{
                                unset($ret);
                                $ret['name']="VAP";
                                $ret['plan']=concatenateAll(parsePlan($bpoint));
                            }
                            
                            $ret['plan']=concatenateAll($ret['plan']);
                        }
                        else{
                            $ret['success']=false;
                            $ret['errcode']="VPL_INVALID_WEEKS";
                            $ret['error']="Vertretungsplan konnte keine Wochen erkennen";
                        }
                    }
                    else{
                        $ret['success']=false;
                        $ret['errcode']="VPL_INVALID_WEEKS";
                        $ret['error']="Vertretungsplan konnte keine Wochen erkennen";
                    }
                }
                else{
                    $ret['success']=false;
                    $ret['errcode']="VPL_NOT_DETECTED";
                    $ret['error']="Vertretungsplan konnte nicht erkannt werden";
                }
            }
        }
        else{
            if($btype==="mon"){
                if(count($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_head ")]'))>0){
                    $head=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_head ")]')[0];
                    $right=$xpath->query('tr/td[@align="right"]/p',$head);
                    if(count($right)>0){
                        $rname=trim(DOMinnerHTML($right[0]));
                        if(strpos(" ".$rname,"<span")){
                            $ret['name']=trim(substr($rname,0,strpos(" ".$rname,"<span")-1));
                        }
                        else
                            $ret['name']=trim($right);
                        if(strpos(" ".$rname,"Stand: "))
                            $ret['lachpl']=date("d.m.Y, H:i",strtotime(substr($rname,strpos(" ".$rname,"Stand: ")+6)));
                    }
                    else{
                        $ret['name']="VAP";
                    }
                }
                else{
                    $ret['name']="VAP";
                }
            }
            else{
                $ret['name']="VAP";
            }
            $pages=[];
            $pages[0]=$ubeta[0]['src'];
            if($btype==="mon"){
                $refr=$xpath->query('//meta[@http-equiv="Refresh"] | //meta[@http-equiv="refresh"] | //meta[@http-equiv="REFRESH"]');
                if(count($refr)>0){
                    $refr=$refr[0];
                    $cnt=$refr->getAttribute("content");
                    if(stripos(" ".$cnt,"URL=")){
                        $newfile=trim(substr($cnt,stripos(" ".$cnt,"URL=")+3));
                        $pages[count($pages)]=dirname($pages[count($pages)-1]).'/'.$newfile;
                        if(!in_array(dirname($pages[count($pages)-1]).'/'.$newfile,$pages)){
                            $nfile=true;
                            while($nfile){
                                $ch=curl_init($pages[count($pages)-1]);
                                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                $html=curl_exec($ch);
                                $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
                                if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
                                    $dom=new DOMDocument();
                                    $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
                                    $xpath=new DOMXPath($dom);
                                    $refr=$xpath->query('//meta[@http-equiv=Refresh] | //meta[@http-equiv=refresh] | //meta[@http-equiv=REFRESH]');
                                    if(count($refr)>0){
                                        $refr=$refr[0];
                                        $cnt=$refr->getAttribute("content");
                                        if(stripos(" ".$cnt,"URL=")){
                                            $newfile=trim(substr($cnt,stripos(" ".$cnt,"URL=")+3));
                                            if(!in_array(dirname($pages[count($pages)-1]).'/'.$newfile,$pages)){
                                                $pages[count($pages)]=dirname($pages[count($pages)-1]).'/'.$newfile;
                                            }
                                        }
                                        else{
                                            $nfile=false;
                                        }
                                    }
                                    else{
                                        $nfile=false;
                                    }
                                }
                                else{
                                    $nfile=false;
                                    array_splice($pages,count($pages)-1,1);
                                }
                            }
                        }
                    }
                }
            }
            $ret['plan']=[];
            for($a=0;$a<count($pages);++$a){
                $ret['plan']=array_merge($ret['plan'],parsePlan($pages[$a]));
            }
            $ret['plan']=concatenateAll($ret['plan']);
        }
    }
    else{
        $ret['success']=false;
        $ret['errcode']="VPL_NOT_DETECTED";
        $ret['error']="Vertretungsplan konnte nicht erkannt werden";
        return;
    }
    if(isset($ret['plan'])){
        $plan=$ret['plan'];
        unset($ret['name']);
        unset($ret['plan']);
        $ret['version']=getInfo("version");
        $ret['copyright_notice']=getInfo("copyright_notice");
        $ret['columns']=[];
        $ret['colors']=$colors;
        $ret['types']=$typs;
        $ret['columns']['all']=getHeaders($plan);
        $ret['columns']['asm']=$asm;
        $ret['columns']['arsm']=[];
        $asm_keys=array_keys($asm);
        for($a=0;$a<count($asm_keys);++$a){
            $ret['columns']['arsm'][$a]['assign']=$asm_keys[$a];
            $ret['columns']['arsm'][$a]['column']=$asm[$asm_keys[$a]];
        }
        $ret['teacher_plan']=$teacher_plan;
        $ret['plan']=$plan;
    }
    if($_GET['format']==="html"){
        header('Content-Type: text/html; charset=utf-8');
        $dom=new DOMDocument();
        $hidden=$dom->createElement("input");
        $hidden->setAttribute("type","hidden");
        $hidden->setAttribute("class","all_data");
        $hidden->setAttribute("value",json_encode($ret));
        $dom->appendChild($hidden);
        foreach($ret['plan'] as $plan_day){
            $day_indic=$dom->createElement("div",$plan_day['day']);
            $day_indic->setAttribute("class","day");
            $dom->appendChild($day_indic);
            if(count($plan_day['news'])>0){
                $news=$dom->createElement("ul");
                $news->setAttribute("class","news");
                foreach($plan_day['news'] as $news_item){
                    $tmpdom=new DOMDocument();
                    $tmpdom->loadHTML(mb_convert_encoding('<li>'.$news_item.'</li>','HTML-ENTITIES','UTF-8'));
                    $news->appendChild($dom->importNode($tmpdom->getElementsByTagName("li")[0],true));
                }
                $dom->appendChild($news);
            }
            $vertr=$dom->createElement("ul");
            $vertr->setAttribute("class","changes-table");
            if($teacher_plan)
                $vertr->setAttribute("class","changes-table teacher-table");
            if(count($plan_day['vertretungen'])>0){
                $header=$dom->createElement("li");
                $header->setAttribute("class","head");
                foreach($ret['columns']['all'] as $header_item){
                    $hi=$dom->createElement("div",$header_item);
                    $header->appendChild($hi);
                }
                $vertr->appendChild($header);
                $lv=null;
                foreach($plan_day['vertretungen'] as $plan_item){
                    if($igr&&(($teacher_plan&&$asm['teacher']!=="false")||(!$teacher_plan&&$asm['class']!=="false"))){
                        $gby=($teacher_plan ? $asm['teacher'] : $asm['class']);
                        if($lv!==$plan_item[$gby]){
                            $gi=$dom->createElement("div");
                            $gi->setAttribute("class","it-group");
                            $vertr->appendChild($gi);
                            $lv=$plan_item[$gby];
                        }
                        $xpath=new DOMXPath($dom);
                        $gi=$xpath->query('*[contains(concat(" ", normalize-space(@class), " "), " it-group ")]',$vertr);
                        $gi=$gi[count($gi)-1];
                        $pi=$dom->createElement("li");
                        $pi->setAttribute("onclick","openDetail(this)");
                        $hidden=$dom->createElement("input");
                        $hidden->setAttribute("type","hidden");
                        $hidden->setAttribute("class","data");
                        $data=[];
                        $data['asm']=$asm;
                        $data['headers']=$ret['columns']['all'];
                        if($asm['type']!=="false"){
                            $tnames=array_column($typs,"name");
                            if(in_array($plan_item[$asm['type']],$tnames))
                                $pi->setAttribute("class","color_".$typs[array_search($plan_item[$asm['type']],$tnames)]['color']);
                            else
                                $pi->setAttribute("class","color_4");
                            
                        }
                        $data['color']=intval(str_replace("color_","",$pi->getAttribute("class")));
                        $data['info']=$plan_item;
                        $hidden->setAttribute("value",json_encode($data));
                        $pi->appendChild($hidden);
                        foreach($ret['columns']['all'] as $header_item){
                            $pii=$dom->createElement("div",$plan_item[$header_item]);
                            if($asm['teacher']===$header_item&&$teacher_plan&&$asm['class']!=="false"){
                                $tmpdom=new DOMDocument();
                                $tmpdom->loadHTML(mb_convert_encoding('<div><div class="tc">'.$plan_item[$header_item].'</div><div class="cls">'.$plan_item[$asm['class']].'</div></div>','HTML-ENTITIES','UTF-8'));
                                $pii=$dom->importNode($tmpdom->getElementsByTagName("div")[0],true);
                            }
                            if($asm['type']===$header_item){
                                $tmpdom=new DOMDocument();
                                $tmpdom->loadHTML(mb_convert_encoding('<div><div class="tp">'.$plan_item[$header_item].'</div><div class="sml">'.$plan_item['small'].'</div></div>','HTML-ENTITIES','UTF-8'));
                                $pii=$dom->importNode($tmpdom->getElementsByTagName("div")[0],true);
                            }
                            if(in_array($header_item,array_values($asm)))
                                $pii->setAttribute("class",array_keys($asm)[array_search($header_item,array_values($asm))]);
                            else if(in_array($header_item,$asm['additional']))
                                $pii->setAttribute("class","additional");
                            $pi->appendChild($pii);
                        }
                        $gi->appendChild($pi);
                    }
                    else{
                        $pi=$dom->createElement("li");
                        $pi->setAttribute("onclick","openDetail(this)");
                        $hidden=$dom->createElement("input");
                        $hidden->setAttribute("type","hidden");
                        $hidden->setAttribute("class","data");
                        $data=[];
                        $data['asm']=$asm;
                        $data['headers']=$ret['columns']['all'];
                        if($asm['type']!=="false"){
                            $tnames=array_column($typs,"name");
                            if(in_array($plan_item[$asm['type']],$tnames))
                                $pi->setAttribute("class","color_".$typs[array_search($plan_item[$asm['type']],$tnames)]['color']);
                            else
                                $pi->setAttribute("class","color_4");
                            
                        }
                        $data['color']=intval(str_replace("color_","",$pi->getAttribute("class")));
                        $data['info']=$plan_item;
                        $hidden->setAttribute("value",json_encode($data));
                        $pi->appendChild($hidden);
                        foreach($ret['columns']['all'] as $header_item){
                            $pii=$dom->createElement("div",$plan_item[$header_item]);
                            if($asm['teacher']===$header_item&&$teacher_plan&&$asm['class']!=="false"){
                                $tmpdom=new DOMDocument();
                                $tmpdom->loadHTML(mb_convert_encoding('<div><div class="tc">'.$plan_item[$header_item].'</div><div class="cls">'.$plan_item[$asm['class']].'</div></div>','HTML-ENTITIES','UTF-8'));
                                $pii=$dom->importNode($tmpdom->getElementsByTagName("div")[0],true);
                            }
                            if($asm['type']===$header_item){
                                $tmpdom=new DOMDocument();
                                $tmpdom->loadHTML(mb_convert_encoding('<div><div class="tp">'.$plan_item[$header_item].'</div><div class="sml">'.$plan_item['small'].'</div></div>','HTML-ENTITIES','UTF-8'));
                                $pii=$dom->importNode($tmpdom->getElementsByTagName("div")[0],true);
                            }
                            if(in_array($header_item,array_values($asm)))
                                $pii->setAttribute("class",array_keys($asm)[array_search($header_item,array_values($asm))]);
                            else if(in_array($header_item,$asm['additional']))
                                $pii->setAttribute("class","additional");
                            $pi->appendChild($pii);
                        }
                        $vertr->appendChild($pi);
                    }
                    
                }
            }
            else{
                $no_vert=$dom->createElement("li");
                $b=$dom->createElement("b","Keine Vertretungen");
                $no_vert->appendChild($b);
                $no_vert->setAttribute("class","novert");
                $vertr->appendChild($no_vert);
            }
            $dom->appendChild($vertr);
        }
        echo $dom->saveHTML();
    }
    else{
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret);
    }
    

    function findAllFrames($ch,$dom){
        $ret=[];
        $xpath=new DOMXPath($dom);
        $aframes=$xpath->query('//iframe | //frame');
        for($a=0;$a<count($aframes);++$a){
            $p=count($ret);
            $ourl=curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
            $expl=explode("/",parse_url($ourl,PHP_URL_PATH));
            if(strpos($expl[count($expl)-1],".")!=false||$expl[count($expl)-1][strlen($expl[count($expl)])-1]!=="/")
                $ourl=parse_url($ourl,PHP_URL_SCHEME).'://'.parse_url($ourl,PHP_URL_HOST).dirname(parse_url($ourl,PHP_URL_PATH)).'/';
            $ch1=curl_init($ourl.$aframes[$a]->getAttribute("src"));
            curl_setopt($ch1,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS
            curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false); // For HTTPS   
            $html=curl_exec($ch1);
            $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
            $dom1=new DOMDocument();
            $dom1->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
            $ret[$p]['src']=curl_getinfo($ch1,CURLINFO_EFFECTIVE_URL);
            $ret[$p]['untistype']=false;
            $xpath=new DOMXPath($dom1);
            $meta=$xpath->query('//meta[@name="GENERATOR"] | //meta[@name="generator"] | //meta[@name="Generator"]')[0];
            if(count($meta)>0){
                if(stripos(" ".$meta->getAttribute("content"),"gp-Untis"))
                    $ret[$p]['untistype']="Untis Alpha";
                else if(stripos(" ".$meta->getAttribute("content"),"Untis")){
                    $ret[$p]['untistype']="Untis Beta";
                    $ret[$p]['untisbetatype']="subst";
                    if(count($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_title ")]'))>0)
                        $ret[$p]['untisbetatype']="mon";
                }
            }
            $ret=array_merge($ret,findAllFrames($ch1,$dom1));
        }
        return $ret;
    }
    function parsePlan($url){
        $day_names=["Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag","Sonntag"];
        $ret=[];
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $html=curl_exec($ch);
        if(curl_getinfo($ch,CURLINFO_RESPONSE_CODE)==200){
            $html =mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
            $html=removeUseless($html);
            $dom=new DOMDocument();
            $dom->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
            $xpath=new DOMXPath($dom);
            $type="subst";
            if(count($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_title ")]'))>0)
                $type="mon";
            if($type==="subst"){
                $tables=$xpath->query('//table');
                for($a=0;$a<count($tables);++$a){
                    $all=$xpath->query('//*');
                    $alldays=$xpath->query('//div[@id="vertretung"]/b | //div[@id="vertretung"]/p/b');
                    $pos=0;
                    $day="";
                    for($b=0;$b<count($all);++$b){
                        if($tables[$a]->isSameNode($all[$b])){
                            $pos=$b;
                            break;
                        }
                    }
                    for($b=$pos-1;$b>=0;--$b){
                        for($c=0;$c<count($alldays);++$c){
                            if($alldays[$c]->isSameNode($all[$b])){
                                $day=$alldays[$c]->textContent;
                                break 2;
                            }
                        }
                    }
                    
                    $spos=strpos(" ".$day,".");
                    $sday=substr($day,$spos);
                    $spos2=strpos(" ".$sday,".");
                    $sposg=$spos+$spos2;
                    $date=substr($day,0,$sposg);
                    $wd=0;
                    for($b=0;$b<count($day_names);++$b){
                        if(strpos(" ".$day,$day_names[$b])){
                            $wd=$b+1;
                            break;
                        }
                    }
                    if($wd==0){
                        $date=strtotime($date.date("Y",microtime(true)));
                    }
                    else{
                        $hfy=false;
                        $sy=intval(date("Y",microtime(true)))+1;
                        while(!$hfy){
                            if(intval(date("N",strtotime($date.$sy)))==$wd){
                                $hfy=true;
                            }
                            else{
                                $sy--;
                            }
                        }
                        $date=strtotime($date.$sy);
                    }
                    $dtd=intval(date("N",$date));
                    $wk=$day_names[$dtd-1];
                    $p=count($ret);
                    if(!preg_match("/^subst$/", $tables[$a]->getAttribute('class'))){
                        $ret[$p]['day']=$wk.", ".date("d.m.",$date);
                        $ret[$p]['dayms']=$date;
                        $ret[$p]['wd']=intval(date("w",$date));
                        $ret[$p]['news']=[];
                        $table=new DOMDocument();
                        $table->loadHTML(mb_convert_encoding($dom->saveHTML($tables[$a]),'HTML-ENTITIES','UTF-8'));
                        for($b=0;$b<count($table->getElementsByTagName("tr"));++$b){
                            if(count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td"))==0)
                                continue;
                            else{
                                for($c=0;$c<count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td"));++$c){
                                    $ret[$p]['news'][count($ret[$p]['news'])]=DOMinnerHTML($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td")[$c]);
                                }
                            }

                        }
                    }
                    else{
                        if($p-1>=0&&!isset($ret[$p-1]['vertretungen'])&&$ret[$p-1]['dayms']==$date){
                            $p--;
                            if(!isset($ret[$p]['news']))
                                $ret[$p]['news']=[];
                        }
                        else{
                            $ret[$p]['day']=$wk.", ".date("d.m.",$date);
                            $ret[$p]['dayms']=$date;
                            $ret[$p]['wd']=intval(date("w",$date));
                            $ret[$p]['news']=[];
                        }
                        $ret[$p]['vertretungen']=[];
                        if(stripos(" ".$tables[$a]->textContent,"Vertretungen sind nicht freigegeben")){
                            array_splice($ret,$p,1);
                        }
                        else if(!stripos(" ".$tables[$a]->textContent,"Keine Vertretungen")){
                            $table=new DOMDocument();
                            $table->loadHTML(mb_convert_encoding($dom->saveHTML($tables[$a]),'HTML-ENTITIES','UTF-8'));
                            $head=[];
                            $b1=0;
                            for($b=0;$b<count($table->getElementsByTagName("tr"));++$b){
                                if(count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th"))>0){
                                    for($c=0;$c<count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th"));++$c){
                                        if(!isset($head[$c])||$head[$c]===""){
                                            $head[$c]=trim($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th")[$c]->textContent);
                                        }
                                        else if(substr($head[$c],strlen($head[$c])-1)==="/"||substr($head[$c],strlen($head[$c])-1)==="-"){
                                            $head[$c].=trim($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th")[$c]->textContent);
                                        }
                                        else{
                                            $head[$c].=" ".trim($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th")[$c]->textContent);
                                        }
                                    }
                                }
                                else{
                                    for($c=0;$c<count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td"));++$c){
                                        $ret[$p]['vertretungen'][$b1][$head[$c]]=$table->getElementsByTagName("tr")[$b]->getElementsByTagName("td")[$c]->textContent;
                                    }
                                    global $asm;
                                    $ret[$p]['vertretungen'][$b1]['small']=createSmall($ret[$p]['vertretungen'][$b1],$asm);
                                    ++$b1;
                                }
    
                            }
                        }
                    }
                }
            }
            else if($type==="mon"){
                $inline_headers=$xpath->query('//td[contains(concat(" ", normalize-space(@class), " "), " inline_header ")]');
                for($a=0;$a<count($inline_headers);++$a){
                    $inline_headers[$a]->parentNode->parentNode->removeChild($inline_headers[$a]->parentNode);
                }
                $tables=$xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " info ")] | //table[contains(concat(" ", normalize-space(@class), " "), " mon_list ")]');
                for($a=0;$a<count($tables);++$a){
                    $all=$xpath->query('//*');
                    $alldays=$xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mon_title ")]');
                    $pos=0;
                    $day="";
                    for($b=0;$b<count($all);++$b){
                        if($tables[$a]->isSameNode($all[$b])){
                            $pos=$b;
                            break;
                        }
                    }
                    for($b=$pos-1;$b>=0;--$b){
                        for($c=0;$c<count($alldays);++$c){
                            if($alldays[$c]->isSameNode($all[$b])){
                                $day=$alldays[$c]->textContent;
                                break 2;
                            }
                        }
                    }
                    $spos=strpos(" ".$day,".");
                    $sday=substr($day,$spos);
                    $spos2=strpos(" ".$sday,".");
                    $sposg=$spos+$spos2;
                    $date=substr($day,0,$sposg);
                    $wd=0;
                    for($b=0;$b<count($day_names);++$b){
                        if(strpos(" ".$day,$day_names[$b])){
                            $wd=$b+1;
                            break;
                        }
                    }
                    if($wd==0){
                        $date=strtotime($date.date("Y",microtime(true)));
                    }
                    else{
                        $hfy=false;
                        $sy=intval(date("Y",microtime(true)))+1;
                        while(!$hfy){
                            if(intval(date("N",strtotime($date.$sy)))==$wd){
                                $hfy=true;
                            }
                            else{
                                $sy--;
                            }
                        }
                        $date=strtotime($date.$sy);
                    }
                    $dtd=intval(date("N",$date));
                    $wk=$day_names[$dtd-1];
                    $p=count($ret);
                    if(preg_match("/^info$/", $tables[$a]->getAttribute('class'))){
                        $ret[$p]['day']=$wk.", ".date("d.m.",$date);
                        $ret[$p]['dayms']=$date;
                        $ret[$p]['wd']=intval(date("w",$date));
                        $ret[$p]['news']=[];
                        $table=new DOMDocument();
                        $table->loadHTML(mb_convert_encoding($dom->saveHTML($tables[$a]),'HTML-ENTITIES','UTF-8'));
                        for($b=0;$b<count($table->getElementsByTagName("tr"));++$b){
                            if(count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td"))==0)
                                continue;
                            else{
                                for($c=0;$c<count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td"));++$c){
                                    $ret[$p]['news'][count($ret[$p]['news'])]=DOMinnerHTML($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td")[$c]);
                                }
                            }

                        }
                    }
                    else{
                        if($p-1>=0&&!isset($ret[$p-1]['vertretungen'])&&$ret[$p-1]['dayms']==$date){
                            $p--;
                            if(!isset($ret[$p]['news']))
                                $ret[$p]['news']=[];
                        }
                        else{
                            $ret[$p]['day']=$wk.", ".date("d.m.",$date);
                            $ret[$p]['dayms']=$date;
                            $ret[$p]['wd']=intval(date("w",$date));
                            $ret[$p]['news']=[];
                        }
                        $ret[$p]['vertretungen']=[];
                        if(stripos(" ".$tables[$a]->textContent,"Vertretungen sind nicht freigegeben")){
                            array_splice($ret,$p,1);
                        }
                        else if(!stripos(" ".$tables[$a]->textContent,"Keine Vertretungen")){
                            $table=new DOMDocument();
                            $table->loadHTML(mb_convert_encoding($dom->saveHTML($tables[$a]),'HTML-ENTITIES','UTF-8'));
                            $head=[];
                            $b1=0;
                            for($b=0;$b<count($table->getElementsByTagName("tr"));++$b){
                                if(count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th"))>0){
                                    for($c=0;$c<count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th"));++$c){
                                        if(!isset($head[$c])||$head[$c]===""){
                                            $head[$c]=trim($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th")[$c]->textContent);
                                        }
                                        else if(substr($head[$c],strlen($head[$c])-1)==="/"||substr($head[$c],strlen($head[$c])-1)==="-"){
                                            $head[$c].=trim($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th")[$c]->textContent);
                                        }
                                        else{
                                            $head[$c].=" ".trim($table->getElementsByTagName("tr")[$b]->getElementsByTagName("th")[$c]->textContent);
                                        }
                                    }
                                }
                                else{
                                    for($c=0;$c<count($table->getElementsByTagName("tr")[$b]->getElementsByTagName("td"));++$c){
                                        $ret[$p]['vertretungen'][$b1][$head[$c]]=$table->getElementsByTagName("tr")[$b]->getElementsByTagName("td")[$c]->textContent;
                                    }
                                    ++$b1;
                                }

                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }
    function removeUseless($string){
        $string=str_replace('<span style="color: #010101">',"",$string);
        $string=str_replace('<span style="color: #FFFFFF">',"",$string);
        $string=str_replace('<span style="color: #0000FF">',"",$string);
        $string=str_replace('<p>',"",$string);
        $string=str_replace('</p>',"",$string);
        $string=str_replace('<span>',"",$string);
        $string=str_replace('</span>',"",$string);
        $string=str_replace('&nbsp;',"",$string);
        $string=str_replace('<strike>',"",$string);
        $string=str_replace('</strike>',"",$string);
        return $string;
    }
    function concatenateAll($plan){
        $splan=[];
        for($a=0;$a<count($plan);++$a){
            $p=count($splan);
            $sdays=array_column($splan,"dayms");
            if(in_array($plan[$a]['dayms'],$sdays)){
                $p=array_search($plan[$a]['dayms'],$sdays);
                for($b=0;$b<count($plan[$a]['news']);++$b){
                    if(!in_array($plan[$a]['news'][$b],$splan[$p]['news']))
                        $splan[$p]['news'][count($splan[$p]['news'])]=$plan[$a]['news'][$b];
                }
                for($b=0;$b<count($plan[$a]['vertretungen']);++$b){
                    if(!in_array($plan[$a]['vertretungen'][$b],$splan[$p]['vertretungen']))
                        $splan[$p]['vertretungen'][count($splan[$p]['vertretungen'])]=$plan[$a]['vertretungen'][$b];
                }
            }
            else{
                $splan[$p]=$plan[$a];
            }
        }
        return $splan;
    }
    function getHeaders($plan){
        $technical_headers=['small','pos','day','cl_number'];
        $headers=[];
        $allv=array_column($plan,"vertretungen");
        $allv1=[];
        for($a=0;$a<count($allv);++$a){
            $allv1=array_merge($allv1,$allv[$a]);
        }
        $keys=[];
        for($a=0;$a<count($allv1);++$a){
            $keys=array_merge($keys,array_keys($allv1[$a]));
        }
        $keys=array_unique($keys);
        foreach($technical_headers as $header){
            if(in_array($header,$keys))
                array_splice($keys,array_search($header,$keys),1);
        }
        return $keys;
    }
    function createSmall($item,$asm){
        $str="";
        if($asm['group']!=="false"&&!untisEmpty($item[$asm['group']]))
            $str=$item[$asm['group']].":";
        if($asm['subj']!=="false"&&$asm['nsubj']!=="false"){
            if(!untisEmpty($item[$asm['subj']])&&!untisEmpty($item[$asm['nsubj']])){
                if($item[$asm['subj']]!==$item[$asm['nsubj']])
                    $str.=(strlen($str)>0 ? " " : "").$item[$asm['subj']]." statt ".$item[$asm['nsubj']];
                else
                    $str.=(strlen($str)>0 ? " " : "").$item[$asm['nsubj']];
            }
            else if(!untisEmpty($item[$asm['subj']])){
                if(strpos(" ".$item[$asm['subj']],"→")!=false){
                    $expl=explode("→",$item[$asm['subj']]);
                    if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " " : "").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " " : "").$expl[0];
                }
                else
                    $str.=(strlen($str)>0 ? " " : "").$item[$asm['subj']];
            }
            else if(!untisEmpty($item[$asm['nsubj']])){
                if(strpos(" ".$item[$asm['nsubj']],"→")!=false){
                    $expl=explode("→",$item[$asm['nsubj']]);
                    if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " " : "").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " " : "").$expl[0];
                }
                else
                    $str.=(strlen($str)>0 ? " " : "").$item[$asm['nsubj']];}
        }
        else if($asm['subj']!=="false"&&!untisEmpty($item[$asm['subj']])){
            if(strpos(" ".$item[$asm['subj']],"→")!=false){
                $expl=explode("→",$item[$asm['subj']]);
                if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " " : "").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " " : "").$expl[0];
            }
            else
                $str.=(strlen($str)>0 ? " " : "").$item[$asm['subj']];
        }
        else if($asm['nsubj']!=="false"&&!untisEmpty($item[$asm['nsubj']])){
            if(strpos(" ".$item[$asm['nsubj']],"→")!=false){
                $expl=explode("→",$item[$asm['nsubj']]);
                if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " " : "").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " " : "").$expl[0];
            }
            else
                $str.=(strlen($str)>0 ? " " : "").$item[$asm['nsubj']];
        }
        if($asm['room']!=="false"&&$asm['nroom']!=="false"){
            if(!untisEmpty($item[$asm['room']])&&!untisEmpty($item[$asm['nroom']])){
                if($item[$asm['room']]!==$item[$asm['nroom']])
                    $str.=(strlen($str)>0 ? " in " : "in ").$item[$asm['room']]." statt ".$item[$asm['nroom']];
                else
                    $str.=(strlen($str)>0 ? " in " : "in ").$item[$asm['nroom']];
            }
            else if(!untisEmpty($item[$asm['room']])){
                if(strpos(" ".$item[$asm['room']],"→")!=false){
                    $expl=explode("→",$item[$asm['room']]);
                    if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[0];
                }
                else
                    $str.=(strlen($str)>0 ? " in " : "in ").$item[$asm['room']];
            }
            else if(!untisEmpty($item[$asm['nroom']])){
                if(strpos(" ".$item[$asm['nroom']],"→")!=false){
                    $expl=explode("→",$item[$asm['nroom']]);
                    if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[0];
                }
                else
                    $str.=(strlen($str)>0 ? " in " : "in ").$item[$asm['nroom']];}
        }
        else if($asm['room']!=="false"&&!untisEmpty($item[$asm['room']])){
            if(strpos(" ".$item[$asm['room']],"→")!=false){
                $expl=explode("→",$item[$asm['room']]);
                if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[0];
            }
            else
                $str.=(strlen($str)>0 ? " in " : "in ").$item[$asm['room']];
        }
        else if($asm['nroom']!=="false"&&!untisEmpty($item[$asm['nroom']])){
            if(strpos(" ".$item[$asm['nroom']],"→")!=false){
                $expl=explode("→",$item[$asm['nroom']]);
                if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " in " : "in ").$expl[0];
            }
            else
                $str.=(strlen($str)>0 ? " in " : "in ").$item[$asm['nroom']];
        }
        if($asm['teacher']!=="false"&&$asm['nteacher']!=="false"){
            if(!untisEmpty($item[$asm['teacher']])&&!untisEmpty($item[$asm['nteacher']])){
                if($item[$asm['teacher']]!==$item[$asm['nteacher']])
                    $str.=(strlen($str)>0 ? " bei " : "bei ").$item[$asm['teacher']]." statt ".$item[$asm['nteacher']];
                else
                    $str.=(strlen($str)>0 ? " bei " : "bei ").$item[$asm['nteacher']];
            }
            else if(!untisEmpty($item[$asm['teacher']])){
                if(strpos(" ".$item[$asm['teacher']],"→")!=false){
                    $expl=explode("→",$item[$asm['teacher']]);
                    if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[0];
                }
                else
                    $str.=(strlen($str)>0 ? " bei " : "bei ").$item[$asm['teacher']];
            }
            else if(!untisEmpty($item[$asm['nteacher']])){
                if(strpos(" ".$item[$asm['nteacher']],"→")!=false){
                    $expl=explode("→",$item[$asm['nteacher']]);
                    if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[0];
                }
                else
                    $str.=(strlen($str)>0 ? " bei " : "bei ").$item[$asm['nteacher']];}
        }
        else if($asm['teacher']!=="false"&&!untisEmpty($item[$asm['teacher']])){
            if(strpos(" ".$item[$asm['teacher']],"→")!=false){
                $expl=explode("→",$item[$asm['teacher']]);
                if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[0];
            }
            else
                $str.=(strlen($str)>0 ? " bei " : "bei ").$item[$asm['teacher']];
        }
        else if($asm['nteacher']!=="false"&&!untisEmpty($item[$asm['nteacher']])){
            if(strpos(" ".$item[$asm['nteacher']],"→")!=false){
                $expl=explode("→",$item[$asm['nteacher']]);
                if($expl[1]!==$expl[0])
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[1]." statt ".$expl[0];
                    else
                        $str.=(strlen($str)>0 ? " bei " : "bei ").$expl[0];
            }
            else
                $str.=(strlen($str)>0 ? " bei " : "bei ").$item[$asm['nteacher']];
        }
        if(count($asm['additional'])>0){
            for($a=0;$a<count($asm['additional']);++$a){
                if(!untisEmpty($item[$asm['additional'][$a]]))
                    $str.=(strlen($str)>0 ? " - " : "").$item[$asm['additional'][$a]];
            }
        }
        return $str;
    }
    function untisEmpty($str){
        return $str===""||$str==="---";
    }
    function DOMinnerHTML(DOMNode $element){ 
        $innerHTML = ""; 
        $children  = $element->childNodes;

        foreach ($children as $child) 
        { 
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML; 
    } 
?>