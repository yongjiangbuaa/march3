<?php
  $params = array_merge($_GET,$_POST);
  if (!isset($params['id'])) {
    exit("error id");
  }

  $id = $params['id'];
  unset($params['id']);

  if (isset($params['fb_locale'])) {
    $locale = $params["fb_locale"];
  }elseif (isset($_SERVER['HTTP_X_FACEBOOK_LOCALE'])) {
    $locale = $_SERVER['HTTP_X_FACEBOOK_LOCALE'];
  }else{
    $locale = "en_US";
  }

  $config_file = "./config/medal.json";
  $dialog_file = "./config/dialog.json";
  if (!file_exists($config_file)) {
    exit("Config File Not Exist");
  }
  if (!file_exists($dialog_file)) {
    exit("Dialog File Not Exist");
  }

  $content = json_decode(file_get_contents($config_file), true);
  $dialog = json_decode(file_get_contents($dialog_file), ture);
  if (!is_array($content)) { exit("Content Format is not correct");}
  if (!is_array($dialog)) { exit("Dialog Format is not correct"); }

  $title = $content[$id]["title"];
  $description = $content[$id]["description"];
  
  $title_dialog = $dialog[$title][$locale];
  $description_dialog = $dialog[$description][$locale];
  $image = $content[$id]["image"];

  $cu = "coq://story/".$id;
  $url = "http://coq.eleximg.com/cdn_res/coq2/index.html";
  $objUrl = "http://169.45.149.91/opengraph/medal.php?id=".$id;
  $canvas_url = addParams($url,$params);
  $deep_linking = addParams($cu,$params);
  $canonicalURL = addParams($objUrl, $params);

  function addParams($url,$params){
    if (count($params) > 0) {
      if(strpos($url, "?") > 0){
        return $url."&".http_build_query($params);
      }else{
        return $url."?".http_build_query($params);
      }
    }else{
      return $url;
    }
  }

  function starToStr($star){
    if ($star == 1) {return "I";}
    else if ($star == 2) {return "II";}
    else if ($star == 3) {return "III";}
    else if ($star == 4) {return "IV";}
    else if ($star == 5) {return "V";}
    else{ return ""; }
  }

  $starStr = starToStr($params["star"]);

    $lang = array("zh_CN","zh_TW","en_US","ko_KR","th_TH","de_DE","ru_RU","pt_BR","ja_JP","fr_FR","es_ES","tr_TR","it_IT","nl_NL","pl_PL" );
?>
<html>
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# clash-of-queens: http://ogp.me/ns/fb/clash-of-queens#">
        <title>Clash of Queens</title>
        <meta property="og:locale" content="<?php echo $locale;?>" />

        <?php 
        foreach ($lang as $key => $value) {
          if ($value != $locale) {
            echo '<meta property="og:locale:alternate" content="'.$value.'"/>';
            echo "\n\t";
          }
        }
        ?>

<?php if ($content[$id]["wide"] != 1) { ?>
        <meta property="fb:app_id" content="979966112074751" />
<?php } ?>

        <meta property="og:title" content="<?php echo sprintf($title_dialog, $starStr); ?>" />
        <meta property="og:description" content="<?php echo sprintf($description_dialog, $params["condition"]); ?>" />
        <meta property="og:image" content="<?php echo sprintf($image, $params["star"]); ?>" />
        <meta property="og:url" content="<?php echo $canonicalURL;?>" />

<?php if ($content[$id]["type"]) { ?>
        <meta property="og:type" content="<?php echo $content[$id]["type"]; ?>" />
<?php }?>

<?php if ($params["star"]) { ?>
        <meta property="game:points" content="<?php echo $params["star"] * 5; ?>" />
<?php }?>

        <!-- App Links -->
          <!-- Android -->
          <meta property="al:android:url" content="<?php echo $deep_linking; ?>" />
          <meta property="al:android:package" content="com.elex.coq.gp" />
          <meta property="al:android:app_name" content="Clash of Queens" />
          <meta property="al:android:class" content="com.clash.of.kings.EmpireActivity" />
          <!-- iOS -->
          <meta property="al:ios:url" content="<?php echo $deep_linking; ?>" />
          <meta property="al:ios:app_name" content="Clash of Queens" />
          <meta property="al:ios:app_store_id" content="1097757301" />
          <!-- Web -->
          <meta property="al:web:should_fallback" content="false" />
          <meta http-equiv="refresh" content="0;url=<?php echo $canvas_url;?>" />
    </head>
    <body>
      Redirecting...
    </body>
</html>
