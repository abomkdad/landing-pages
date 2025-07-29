<?php





// start check domain license

// set domains you can add multiple  doamins 
if(isset($domainLicenseByDl)) unset($domainLicenseByDl);
$domainLicenseByDl[]="*";


function requestMyServInfo() {

  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}


function inArrayWildcard($needle, $haystack) {
    # this function allows wildcards in the array to be searched
    foreach ($haystack as $value) {
        if (true === fnmatch($value, $needle)) {
            return true;
        }
    }
    return false;
}


$serverInfoByDl = requestMyServInfo();
if(!inArrayWildcard($serverInfoByDl['HOST'] , $domainLicenseByDl) )    
exit("Error c709: invalid license for domain : $serverInfoByDl[HOST]");
 
// end check domain license


// start a  session
session_start();


// start site view
if(VIEW=='site'){

// configuration files
require_once "./config/config.php";
require_once "./class/db.php";
require_once "./class/time.php";
require_once "./class/template.php";
require_once "./class/app.php";
require_once "./class/form.php";
require_once "./class/text.php";
require_once "./class/setting.php";
require_once "./class/storage.php";
require_once "./class/mail.php";
require_once "./class/miscellaneous.php";
require_once "./class/search.php";
require_once "./class/lang.php";


 
 
 // load language settings into array 
$settingData=setting::getSettingArray();
$langData=lang::getLangArray($settingData['system']['lang']);


// set time zone
date_default_timezone_set($settingData['system']['timeZone']);




// validate Request url against system url 
miscellaneous::validateRequesturl();

// auto convert less to css under development environment 
if($settingData['system']['environment']=='development')
template::compileLessToCssDevelopmentEnvironment();


 

// load all app classes
$myApps = new app;
$allApps = $myApps -> getAllAppList();
 for ($a = 0; $a < count($allApps); $a++) {
	$currentAppPath = "./app/$allApps[$a]";
	$currentAppPackage=$allApps[$a];
	
 	
	if (file_exists("$currentAppPath/{$allApps[$a]}.class.php")) {
		require_once "$currentAppPath/{$allApps[$a]}.class.php";
	}
}
 



if($_GET['account']){
 $user=new user;
 $user->loginRequired();
}






//  section definition
$section['all']=TRUE;
if (!$_GET['app']) $section['index']=TRUE;
if ($_GET['app']){
	$explode_app=explode('.',$_GET['app']);
	$getApp['package']=$explode_app[0];
	$getApp['oper']=$explode_app[1];
	$getApp['data']=$explode_app[2];
	$getApp['page']=$explode_app[3];
	if(empty($getApp['page']))$getApp['page']=1;
	}


 


for ($a=0; $a<count($allApps);$a++){
$currentAppName=$langData[$allApps[$a]]['app_name'];
$currentAppPackage="$allApps[$a]";
$currentAppPath="./app/$allApps[$a]";
if($currentAppPackage==$getApp['package'] || $currentAppPackage==$_GET['forApp']) $currentAppActive="active"; else $currentAppActive=null;

if (file_exists("$currentAppPath/site.php")){
if($getApp['package']==$currentAppPackage){ $section['app']=TRUE;   }else{ $section['app']=FALSE ; }
require_once"$currentAppPath/site.php";
}





// run sesstion note 
$siteData['content'].=text::ShowPreviousPageNote();

if($currentAppPackage==$getApp['package'] && $getApp['oper']!=""){
if (file_exists("$currentAppPath/siteOper/$getApp[oper].php")){
require_once"$currentAppPath/siteOper/$getApp[oper].php";
}else{ http_response_code(404); $siteData['content'].=text::warmNote("Error c700: No file in the oper path @ $currentAppPath/siteOper/$getApp[oper].php "); }
	
}
}


// runAtExtractSite
app::runAtExtractSite();



//start load site graphic code 
if(empty($siteData['title']) || !isset($_GET['app']))$siteData['title']=$settingData['system']['name'];



if(empty($siteData['meta']) || !isset($_GET['app']) ){
$siteData['meta'].=seo::metaDescription("{$settingData[seo][metaDescription]}");
$siteData['meta'].=seo::openGraph("{$settingData[system][name]}",null,$settingData['system']['url']);

}


// contentTop 
if(empty($siteData['contentTop'])) $siteData['contentTop'] = '';
 


// head
if(empty($siteData['head'])) $siteData['head'] = '';

// search form
if(empty($siteData['searchForm'])) $siteData['searchForm'] = '';


if(empty($siteData['content'])) $siteData['content'] = '';
if(empty($siteData['siteList'])) $siteData['siteList'] = '';



$template=new template;


// proccess breadcrumb 
$SMV['breadcrumb']=template::getBreadcrumb();
$SMV['socialButton']=template::socialButton();

// critical css 
$siteData['criticalCss']=file_get_contents('theme/site.css');

// Extract Site
$siteTemplate=$template->load("theme/main_layout");


// cookieless Domain
$siteTemplate=seo::cookielessDomain($siteTemplate);

echo template::minifyHtml($siteTemplate); 


	
	
}
// end site view 
















// start admin view
if(VIEW=='admin'){


// configuration files
require_once "../config/config.php";
require_once "../class/app.php";
require_once "../class/time.php"; 
require_once"../class/template.php";
require_once "../class/form.php"; 
require_once "../class/db.php"; 
require_once "../class/text.php"; 
require_once "../class/setting.php"; 
require_once "../class/storage.php"; 
require_once "../class/mail.php"; 
require_once "../class/miscellaneous.php";
require_once "../class/search.php";
require_once "../class/lang.php";
require_once "../class/format.php";




// load language settings into array 
$settingData=setting::getSettingArray();
$langData=lang::getLangArray($settingData['system']['lang']);

// set time zone
date_default_timezone_set($settingData['system']['timeZone']);


// validate Request url against system url 
miscellaneous::validateRequesturl();


// auto convert less to css under development environment 
if($settingData['system']['environment']=='development')
template::compileLessToCssDevelopmentEnvironment();

 
// load all app classes 
$myApps=new app;
$allApps=$myApps->getAllAppList();
for ($a=0; $a<count($allApps);$a++){
$currentAppPath="../app/$allApps[$a]";
$currentAppPackage=$allApps[$a];


if (file_exists("$currentAppPath/{$allApps[$a]}.class.php")){
require_once "$currentAppPath/{$allApps[$a]}.class.php";
}
}
 




   
// bulid css direction according language settings 
if($langData['system']['lang_direction']=="rtl") $siteData['css'][]="./theme/rtl.css.less";
if($langData['system']['lang_direction']=="ltr") $siteData['css'][]="./theme/ltr.css.less";

  
  
  
  
 $user=new user;
 $user->loginRequired();
 

 

//  section definition
$section['all']=TRUE;
if (!$_GET['app']) $section['index']=TRUE;
if ($_GET['app']){
	$explode_app=explode('.',$_GET['app']);
	$getApp['package']=$explode_app[0];
	$getApp['oper']=$explode_app[1];
	$getApp['data']=$explode_app[2];
	$getApp['page']=$explode_app[3];
	if(empty($getApp['page']))$getApp['page']=1;
	}




// load user privilege for all the s
$currentUser=new user;
$currentUser->id=$_SESSION['userId'];
$userPrivilegeArray=$currentUser->privilegeArray();

if(empty($userPrivilegeArray)) $currentUser->logout();


// load applications list

$myApps=new app;
$allApps=$myApps->getAllAppList($userPrivilegeArray);
 

for ($a=0; $a<count($allApps);$a++){
$currentAppName=$langData[$allApps[$a]]['app_name'];
$currentAppPackage="$allApps[$a]";
$currentAppPath="../app/$allApps[$a]";
if($currentAppPackage==$getApp['package'] || $currentAppPackage==$_GET['forApp']) $currentAppActive="active"; else $currentAppActive=null;
if (file_exists("$currentAppPath/admin.php")){
if($getApp['package']==$currentAppPackage){ $section['app']=TRUE;   }else{ $section['app']=FALSE ; }

require_once "$currentAppPath/admin.php";
}



if($currentAppPackage==$getApp['package'] && $getApp['oper']!=""){
// run sesstion note
 $siteData['content'].=text::ShowPreviousPageNote();

if (file_exists("$currentAppPath/adminOper/$getApp[oper].php")){
require_once "$currentAppPath/adminOper/$getApp[oper].php";
}else{ http_response_code(404); $siteData['content'].=text::warmNote("Error c700: No file in the oper path @ $currentAppPath/adminOper/$getApp[oper].php "); }
	
}
}


// runAtExtractSite
app::runAtExtractSite();

// generate quick add menu
for($i=0; $i<count($quickAddMenu['title']); $i++){
$quickAddMenuList.="<a href='{$quickAddMenu[url][$i]}'>{$quickAddMenu[title][$i]}</a>";
}

 
// get current user info
$userInfo=$currentUser->Info();

// Extract Site
$template=new template;
$SMV['title']="CMS MyAdmin";
$SMV['userName']="$userInfo[name]";
$SMV['userImage']="$userInfo[image]";
$SMV['quickAddMenu']="$quickAddMenuList";

$SMV['content']="$siteData[content]";
$SMV['siteList']="$siteData[appList]";

// critical css 
$siteData['criticalCss']=file_get_contents('theme/admin.css');

// Extract Site
$siteTemplate=$template->load("theme/main_layout");



echo $siteTemplate;




}
// end admin view 
