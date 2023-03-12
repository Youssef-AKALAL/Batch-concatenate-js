<!DOCTYPE html>
<?php

@$lpsku   = $id_path;
@$lpinfo  = fetch_data_user('Template', 'WHERE LP_SKU = ?', array($lpsku));

echo 'AKALAL '.$id_path;

if (count($lpinfo) == 0) {
  header("Location: ../"); // 404
  exit();
}

$lp_status = $lpinfo[0]['LP_Status'];
$lpid      = $lpinfo[0]['ID'];
$lpdata    = json_decode($lpinfo[0]['LP_Data']);
$lp_style  = json_decode($lpinfo[0]['LP_Style']);
$pricing   = er_getPrice($lpinfo[0]['LP_Price'], false);
$pricingx  = er_getPrice($lpinfo[0]['LP_Pricex'], false);
$lp_price_usd  = round($lpinfo[0]['LP_Price']);
$pro_info  = fetch_data_user('Products', 'WHERE ID = ?', array($lpinfo[0]['Productid']));
$list_offers = \Dropify\UserModels\Offer::where('product_id', $lpinfo[0]['Productid'])->get();
$scriptSet = [];
$skippOffers = true;

// Sales Funnels Languages

$lp_lang   = $lp_style->Settings[0]->lang;

switch ($lp_lang) {
    # English (United State)
  case 'English':
    load_lang('en', 'salesfunnels');
    $countries_list = load_config('isoCountries_English');
    $current_lang = "en";
    break;
    # Arabic (Native)             
  case 'Arabic':
    load_lang('ar', 'salesfunnels');
    $countries_list = load_config('isoCountries_Arabic');
    $current_lang = "ar";
    break;
    # French           
  case 'French':
    load_lang('fr', 'salesfunnels');
    $countries_list = load_config('isoCountries_French');
    $current_lang = "fr";
    break;
  default:
    load_lang('en', 'salesfunnels');
    $countries_list = load_config('isoCountries_English');
    $current_lang = "en";
    break;
}

$symbol = lang($er_getVisitorCurrencySymbolVal) . ' ';

$success_modal_title = $lpinfo[0]['LP_ThanksTitle'] ?? lang('alright');
$success_modal_message = $lpinfo[0]['LP_Thanks'] ?? lang('order_sended');

$time2call_title =  $lpinfo[0]['LP_Time2callTitle'] ?? lang('lp_time2callTitle');
$time2call_message = $lpinfo[0]['LP_Time2callMessage'] ?? lang('lp_time2callMessage');

$priceDefault = [
  'price' => $pricing,
  'compare_price' => $pricingx
];
?>

<?php
$prices = [];
$variant_prices = \Dropify\UserModels\Variant::where('product_id', $lpinfo[0]['Productid'])->get();
$variantsLp = \Dropify\UserModels\LpVariant::where('lp_id', $lpinfo[0]['ID'])->get();

foreach ($variant_prices as $v) {
  $vLp = $variantsLp->where('combination', $v->combination)->first() ?? $v;
  $prices[$v->combination] = [
    "price" => er_getPrice($vLp->price, false),
    "compare_price" => er_getPrice($vLp->compare_price, false),
    "sku" => $v->sku,
    "color" => $v->color,
  ];
}
$offers_data = [];
foreach ($list_offers as $offer) {
  $offers_data[] = [
    "qty" => $offer->qty,
    "price" => er_getPrice($offer->price, false),
    "skus" => isset($offer->skus) ? explode(',', $offer->skus) : null
  ];
}

?>

<html lang="<?= $current_lang; ?>" dir="<?= $lp_style->Settings[0]->lang == 'Arabic' ? 'rtl' : 'ltr' ?>">

<head>

  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <!-- <meta name="viewport"         content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> -->
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10, user-scalable=yes">
  <meta name="author" content="Dropify inc,<?= $store_info[0]['legal_name'] ?? '' ?>">
  <meta name="country" content="United Arab Emirates">
  <meta name="format-detection" content="telephone=no" />
  <meta name="copyright" content="© <?= date('Y') ?> dropify All Rights Reserved">
  <meta name="keywords" content="<?= $store_settings[0]['seo_keyword'] ?? ''; ?>,<?= $lpinfo[0]['LP_Seokeywords'] ?? ''; ?>">
  <meta name="description" content="<?= $store_settings[0]['LP_SeoDescr'] ?? lang('sf_lp_seoDescr'); ?>">
  <meta name="dns-prefetch" content="<?= get_domain_link($store_info[0]['Storeid']); ?>">
  <meta name="robots" content="all">
  <meta name="AKALAL" content="all">
  <meta name="googlebot" content="all">
  <meta name="revisit" content="1 day">
  <meta name="revisit-after" content="1 day">
  <meta name="pagename" content="<?= $store_settings[0]['store_title'] ?? ''; ?>">
  <meta name="twitter:card" content="<?= $lpinfo[0]['LP_Title'] ?? ''; ?>" />
  <meta name="twitter:site" content="<?= get_domain_link($store_info[0]['Storeid']); ?>" />
  <meta name="twitter:creator" content="Dropify inc,<?= $store_info[0]['legal_name'] ?? '' ?>" />
  <meta name=”twitter:title” content="<?= $store_settings[0]['seo_keyword'] ?? ''; ?>,<?= $lpinfo[0]['LP_Seokeywords'] ?? ''; ?>">
  <meta name=”twitter:description” content="<?= $lpinfo[0]['LP_SeoDescr'] ?? ''; ?>">
  <meta name=”twitter:image” content="<?= $store_settings[0]['store_logo'] ?? ''; ?>">
  <meta property="og:description" content="<?= $lpinfo[0]['LP_SeoDescr'] ?? ''; ?>">
  <meta property="og:title" content="<?= $lpinfo[0]['LP_Title'] ?? ''; ?>">
  <meta property="og:image" content="<?= $store_settings[0]['store_logo'] ?? ''; ?>" />
  <meta property='og:url' content='<?= get_domain_link($store_info[0]['Storeid']); ?>' />
  <meta property='og:site_name' content="<?= $store_info[0]['subdomain'] ?? '' ?>" />
  <meta property='og:type' content="Sales Funnel">


  <title><?= $lpinfo[0]['LP_Title']; ?></title>
  <!-- Icons -->

  <link rel="stylesheet" href="<?= $cdn_funnel_path ?>/css/sweetalert2.min.css" />
  <link type="text/css" href="<?= $link ?>/assets_landing/css/main.css" rel="stylesheet">
  <link type="text/css" href="<?= $link ?>/assets_landing/css/radio.css" rel="stylesheet">

  <?php if ($lp_lang == 'Arabic') : ?>
    <link type="text/css" href="<?= $link ?>/assets_landing/css/radio_rtl.css" rel="stylesheet">
  <?php endif; ?>




  <?php if ($lp_status == 'Active') : ?>

    <script src="https://kit.fontawesome.com/c14af102af.js" crossorigin="anonymous"></script>

    <?php $not_google_font = ['BienSportArabic', 'DINNextLTArabic'] ?>
    <?php if (in_array($lp_style->StyleInfo[0]->{'font-family'}, $not_google_font) == false) : ?>
      <link href="https://fonts.googleapis.com/css2?family=<?= $lp_style->StyleInfo[0]->{'font-family'} ?>:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link type="text/css" href="<?= $cdn_funnel_path; ?>/cdn_funnels_example/countrypicker.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
      // Passive event listeners
      jQuery.event.special.touchstart = {
        setup: function(_, ns, handle) {
          this.addEventListener("touchstart", handle, {
            passive: !ns.includes("noPreventDefault")
          });
        }
      };
      jQuery.event.special.touchmove = {
        setup: function(_, ns, handle) {
          this.addEventListener("touchmove", handle, {
            passive: !ns.includes("noPreventDefault")
          });
        }
      };
    </script>

    <script type="text/javascript">
      var base_url = "<?= $store_link; ?>";
      var alright = "<?= $success_modal_title; ?>";
      var success_order = "<?= $success_modal_message; ?>";
      var ok = "<?= lang('ok'); ?>";
      var error = "<?= lang('error'); ?>";
      var correct_error = "<?= lang('correct_error'); ?>";
      var symbol = "<?= $symbol; ?>";
      var sf_justone = '<?= lang('sf_justone'); ?>';

      var sf_buyjustone = '<?= lang('sf_buyjustone'); ?>';
      var sf_iwanttobuy = '<?= lang('sf_iwanttobuy'); ?>';
      var only_for = '<?= lang('only_for'); ?>';
    
    </script>
    <script src="<?= $cdn_funnel_path ?>/js/sweetalert2.min.js"></script>

    <?php
    $GooglePixel = isset($lpinfo[0]['funnel_google_pixel']) ? $lpinfo[0]['funnel_google_pixel'] : $store_settings[0]['GooglePixel'];
    $pro_snap = count(json_decode($lpinfo[0]['funnel_snap_pixel']) ?? []) > 0 ? $lpinfo[0]['funnel_snap_pixel'] : $store_settings[0]['SnapPixel'];
    $pro_fb = count(json_decode($lpinfo[0]['funnel_facebook_pixel']) ?? []) > 0 ? $lpinfo[0]['funnel_facebook_pixel'] : $store_settings[0]['FacebookPixel'];
    $pro_tiktok = count(json_decode($lpinfo[0]['funnel_tiktok_pixel']) ?? []) > 0 ? $lpinfo[0]['funnel_tiktok_pixel'] : $store_settings[0]['TiktokPixel'];

    ?>
    <?php if ($lpinfo[0]['lp_pixels_Status'] == 'Active') : ?>

      <?php if (isset($GooglePixel)) : ?>

        <?php
        $pixels = explode(PHP_EOL, trim($GooglePixel));
        $trim_pixels = array_map("trim", array_filter($pixels));
        ?>

        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $trim_pixels[0]; ?>"></script>
        <script>
          window.dataLayer = window.dataLayer || [];

          function gtag() {
            window.dataLayer.push(arguments);
          }
          gtag('js', new Date());
          const ids_google = ["<?= implode('","', $trim_pixels); ?>"];

          for (id in ids_google) {
            gtag('config', id);
          }
        </script>

      <?php endif; ?>

      <?php if (isset($pro_fb)) : ?>

        <?php
        $pixels = json_decode($pro_fb) ?? [];
        if (count($pixels) > 0) :
        ?>

          <!-- Facebook Pixel Code -->
          <script>
            ! function(f, b, e, v, n, t, s) {
              if (f.fbq) return;
              n = f.fbq = function() {
                n.callMethod ?
                  n.callMethod.apply(n, arguments) : n.queue.push(arguments)
              };
              if (!f._fbq) f._fbq = n;
              n.push = n;
              n.loaded = !0;
              n.version = '2.0';
              n.queue = [];
              t = b.createElement(e);
              t.async = !0;
              t.src = v;
              s = b.getElementsByTagName(e)[0];
              s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
              'https://connect.facebook.net/en_US/fbevents.js');
            <?php
            foreach ($pixels as $pixel) {
            ?>
              fbq('init', '<?= trim($pixel->fba); ?>');
            <?php } ?>
            fbq('track', 'PageView');
          </script>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (isset($pro_snap)) : ?>
        <?php
        $pixels = json_decode($pro_snap) ?? [];
        if (count($pixels) > 0) :
        ?>

          <!-- SnapChat Pixel Code -->
          <script type='text/javascript'>
            (function(e, t, n) {
              if (e.snaptr) return;
              var a = e.snaptr = function() {
                a.handleRequest ? a.handleRequest.apply(a, arguments) : a.queue.push(arguments)
              };
              a.queue = [];
              var s = 'script';
              r = t.createElement(s);
              r.async = !0;
              r.src = n;
              var u = t.getElementsByTagName(s)[0];
              u.parentNode.insertBefore(r, u);
            })(window, document,
              'https://sc-static.net/scevent.min.js');
            <?php
            foreach ($pixels as $pixel) {
            ?>
              snaptr('init', '<?= trim($pixel->snapa); ?>');
            <?php } ?>
            snaptr('track', 'PAGE_VIEW');
          </script>

        <?php endif; ?>

      <?php endif; ?>


      <?php if (isset($pro_tiktok)) : ?>

        <?php

        $pixels = json_decode($pro_tiktok) ?? [];

        if (count($pixels) > 0) :

        ?>

          <!-- Tiktok Pixel Code -->

          <script>
            ! function(w, d, t) {
              w.TiktokAnalyticsObject = t;
              var ttq = w[t] = w[t] || [];
              ttq.methods = ["page", "track", "identify", "instances", "debug", "on", "off", "once", "ready", "alias", "group", "enableCookie", "disableCookie"], ttq.setAndDefer = function(t, e) {
                t[e] = function() {
                  t.push([e].concat(Array.prototype.slice.call(arguments, 0)))
                }
              };
              for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
              ttq.instance = function(t) {
                for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]);
                return e
              }, ttq.load = function(e, n) {
                var i = "https://analytics.tiktok.com/i18n/pixel/events.js";
                ttq._i = ttq._i || {}, ttq._i[e] = [], ttq._i[e]._u = i, ttq._t = ttq._t || {}, ttq._t[e] = +new Date, ttq._o = ttq._o || {}, ttq._o[e] = n || {};
                n = document.createElement("script");
                n.type = "text/javascript", n.async = !0, n.src = i + "?sdkid=" + e + "&lib=" + t;
                e = document.getElementsByTagName("script")[0];
                e.parentNode.insertBefore(n, e)
              };
              <?php

              foreach ($pixels as $pixel) {
              ?>
                ttq.load('<?= trim($pixel->tiktoka); ?>');
              <?php } ?>

              ttq.page();
            }(window, document, 'ttq');
          </script>

        <?php endif; ?>
      <?php endif; ?>

    <?php endif; ?>

    <?= $theme_settings[0]['header_text'] ?? ''; ?>

    <?php if (isAppActive(10)) : ## Check Hotjar app if active or not 
    ?>

      <?php if ($hotjar_app_info[0]['display_on'] == 'funnels' || $hotjar_app_info[0]['display_on'] == 'both') : ?>


        <?php if (isset($hotjar_app_info[0]['hotjar_code'])) : ?>

          <!-- Hotjar Tracking Code -->

          <script>
            (function(h, o, t, j, a, r) {
              h.hj = h.hj || function() {
                (h.hj.q = h.hj.q || []).push(arguments)
              };
              h._hjSettings = {
                hjid: <?= $hotjar_app_info[0]['hotjar_code']; ?>,
                hjsv: 6
              };
              a = o.getElementsByTagName('head')[0];
              r = o.createElement('script');
              r.async = 1;
              r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
              a.appendChild(r);
            })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv=');
          </script>

        <?php endif; ?>

      <?php endif; ?>

    <?php endif; ?>

</head>

<body>

  <style type="text/css">
    <?php if (isset($lp_style->StyleInfo[0])) : ?>body {

      <?php foreach ($lp_style->StyleInfo[0] as $k0 => $v0) : ?><?= $k0; ?>: <?= $v0; ?> !important;

      <?php endforeach; ?>
    }

    <?php endif; ?><?php if (isset($lp_style->Settings[0]->css_custom)) : ?><?= $lp_style->Settings[0]->css_custom; ?><?php endif; ?>
  </style>

  <?php if (isAppActive(9)) : ## Check messagner app if active or not 
  ?>

    <?php if ($fb_messanger_app_info[0]['display_chat_on'] == 'only_funnels' || $fb_messanger_app_info[0]['display_chat_on'] == 'both') : ?>

      <?php if (isset($fb_messanger_app_info[0]['plugin_code'])) : ?>

        <!-- Messenger Chat Plugin Code -->

        <div id="fb-root"></div>

        <!-- Your Chat Plugin code -->

        <div id="fb-customer-chat" class="fb-customerchat"></div>

        <script>
          var chatbox = document.getElementById('fb-customer-chat');
          chatbox.setAttribute("page_id", "<?= $fb_messanger_app_info[0]['plugin_code'] ?>");
          chatbox.setAttribute("attribution", "biz_inbox");
        </script>

        <!-- Your SDK code -->

        <script>
          window.fbAsyncInit = function() {
            FB.init({
              xfbml: true,
              version: 'v15.0'
            });
          };

          (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
            fjs.parentNode.insertBefore(js, fjs);
          }(document, 'script', 'facebook-jssdk'));
        </script>

      <?php endif; ?>

    <?php endif; ?>

  <?php endif; ?>

  <div class="landing-page">

    <?php

    // Add new visitor record

    if ((!isset($_SESSION["VC_$lpid"]) || $_SESSION["VC_$lpid"] != true) && $lp_status == "Active") {
      add_view_record($lpid, $lpinfo[0]['Productid'], 'VC', 'funnels');
    }

    ?>

    <?php foreach ($lpdata as $key => $value) : $lp = $value[1]; ?>

      <?php if (is_null($lp)) continue; ?>

      <?php if ($lp->type == 'notice') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'Inactive') : ?>
          <!-- Notice Bar  -->
          <section id="section_<?= $value[0]; ?>" class="section_<?= $value[0]; ?> lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;">
            <?php if (isset($lp->data->link)) : ?>
              <!-- rmoved from buttom tag : "  -->
              <a id="section_<?= $value[0]; ?>_1" href="<?= $lp->data->link ?>" class="d-block" style="<?= $lp->style ?? ""; ?>" aria-label="<?= lang('sf_notice_bar_section') ?>">
                <?= replace_price($lp->data->title, $pricing, $pricingx, $symbol, $lp_lang); ?>
              </a>
            <?php else : ?>
              <!-- rmoved from buttom tag : id="section_<?= $value[0]; ?>"  -->
              <div id="section_<?= $value[0]; ?>_1" style="<?= $lp->style ?? ""; ?>">
                <?= replace_price($lp->data->title, $pricing, $pricingx, $symbol, $lp_lang); ?>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($lp->type == 'media') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Media Youtube  -->

          <section id="section_<?= $value[0]; ?>" class="lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;<?= $lp->style ?? ""; ?>">
            <div class="<?= $lp->data->container ?? ""; ?>">
              <div class="row justify-content-md-center">

                <div class="col-md-6 mt-0 text-center">

                  <h4 style="<?= $lp->style ?? ""; ?>">
                    <?= replace_price($lp->data->title, $pricing, $pricingx, $symbol, $lp_lang); ?>
                  </h4>
                  <?php if (isset($lp->data->sub_title)) : ?>
                    <h5> <?= $lp->data->sub_title ?? ""; ?> </h5>
                  <?php endif; ?>

                  <?php if ($lp->data->popup == 'yes') : ?>

                    <link href="<?= $cdn_funnel_path; ?>/cdn_funnels_example/lightcase.min.css" rel="stylesheet">

                    <div class="video_ex">
                      <div class="iframe-container">
                        <div class="imagex position-relative mb-4 mb-lg-0 wow fadeInDown text-center" data-wow-delay="0.3s">
                          <img src="<?= get_image_link($lp->data->image); ?>" class="img-fluid img-overly" style="border-radius: 8px;" alt="<?= $lp->data->title . '' . lang('sf_youtube_cover'); ?> " />
                          <a class="play-video" href="https://www.youtube.com/embed/<?= get_youtube_id($lp->data->link); ?>" data-rel="lightcase:myCollection" area-label="<?= $lp->data->title . '' . lang('sf_youtube_cover'); ?> ">
                            <i class="fas fa-play"></i>
                          </a>
                        </div>
                      </div>
                    </div>

                    <script src="<?= $cdn_funnel_path; ?>/cdn_funnels_example/lightcase.min.js"></script>

                    <script type="text/javascript">
                      $('[data-rel^=lightcase]').lightcase();
                    </script>

                  <?php else : ?>
                    <?php
                    $youtub_id = get_youtube_id($lp->data->link);
                    $control  = $lp->data->controls != 'active' ? "&controls=0" : false;
                    $start = $lp->data->start > 0 ? "&start=" . $lp->data->start : false;
                    $url = "https://www.youtube.com/embed/" . $youtub_id . "?x" . $control . $start;

                    ?>
                    <iframe src=<?= $url ?> width="100%" height="315" title="<?= isset($lp->data->title) ? $lp->data->title : lang('sf_youtube_title')  ?>" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture;autoplay" srcdoc="
                    <style>
                      *{padding:0;margin:0;overflow:hidden}
                      html,body{height:100%}
                      img,span{position:absolute;width:100%;top:0;bottom:0;margin:auto}
                      span{height:1.5em;text-align:center;font:48px/1.5 sans-serif;color:white;text-shadow:0 0 0.5em black}
                    </style>
                    <a href=<?= $url ?>>
                      <img src=https://img.youtube.com/vi/<?= $youtub_id ?>/hqdefault.jpg 
                      alt=<?= $lp->data->title . $lp->data->sub_title ?>>
                      <span>▶</span>
                    </a>" allowfullscreen></iframe>

                  <?php endif; ?>


                </div>
              </div>
            </div>
          </section>

        <?php endif; ?>
      <?php endif; ?>


      <?php if ($lp->type == 'slider') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Slider Images  -->

          <section id="section_<?= $value[0]; ?>" class="lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;">
            <div class="<?= $lp->data->container ?? ""; ?>">

              <div class="row justify-content-md-center">

                <div class="col-md-6 text-center">
                  <?php if (isset($lp->data->sub_title)) : ?>
                    <h5> <?= $lp->data->sub_title ?? ""; ?> </h5>
                  <?php endif; ?>
                  <?php if (isset($lp->data->title)) : ?>
                    <h4 class="section_<?= $value[0]; ?>" style="<?= $lp->style ?? ""; ?>">
                      <?= replace_price($lp->data->title, $pricing, $pricingx, $symbol, $lp_lang); ?>
                    </h4>
                  <?php endif; ?>
                  <?php

                  $i = 0;

                  $a = 0;

                  $imgexlopode = explode(',', $lp->data->images);

                  $slider_cotrol = $lp->data->slider_control ?? "";

                  ?>

                  <div id="slider-hb<?= $value[0]; ?>" class="carousel slide" data-ride="carousel">

                    <?php if ($slider_cotrol == 'both' || $slider_cotrol == 'indicators') : ?>
                      <ol class="carousel-indicators">
                        <?php foreach ($imgexlopode as $k1 => $v1) : ?>

                          <li data-target="#slider-hb<?= $value[0]; ?>" data-slide-to="<?= $a; ?>" class="<?= $i == 0 ? 'active' : false; ?>"></li>

                          <?php $a++; ?>
                        <?php endforeach; ?>
                      </ol>
                    <?php endif; ?>

                    <div class="carousel-inner">

                      <?php foreach ($imgexlopode as $k1 => $v1) : ?>

                        <div class="carousel-item <?= $i == 0 ? 'active' : false; ?>">
                          <img class="d-block w-100" src="<?= get_image_link($v1); ?>" alt="<?= lang("sf_carousel_image"); ?>" />
                        </div>

                        <?php $i++; ?>

                      <?php endforeach; ?>

                    </div>

                  </div>

                  <?php if ($slider_cotrol == 'both' || $slider_cotrol == 'arrows') : ?>

                    <a class="carousel-control-prev" href="#slider-hb<?= $value[0]; ?>" role="button" data-slide="prev" area-label="<?= lang('sf_carousel_label'); ?>">
                      <span class="carousel-control-prev-icon" aria-hidden="true" title="<?= lang('sf_carousel_label'); ?>"></span>
                      <span class="sr-only">Previous</span>
                    </a>

                    <a class="carousel-control-next" href="#slider-hb<?= $value[0]; ?>" role="button" data-slide="next" area-label="<?= lang('sf_carousel_label'); ?>">
                      <span class="carousel-control-next-icon" aria-hidden="true" title="<?= lang('sf_carousel_label'); ?>"></span>
                      <span class="sr-only">Next</span>
                    </a>

                  <?php endif; ?>

                </div>

              </div>

            </div>
          </section>


        <?php endif; ?>
      <?php endif; ?>

      <?php if ($lp->type == 'single_image') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Single Image  -->

          <section id="section_<?= $value[0]; ?>" class="lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;">
            <div class="<?= $lp->data->container ?? ""; ?>">

              <div class="row justify-content-md-center">

                <div class="col-md-6 text-center">
                  <?php if (isset($lp->data->sub_title)) : ?>
                    <h5> <?= $lp->data->sub_title ?? ""; ?> </h5>
                  <?php endif; ?>

                  <?php if (isset($lp->data->title)) : ?>

                    <h4 class="text-center" style="<?= $lp->style ?? ""; ?>">
                      <?= $lp->data->title; ?>
                    </h4>

                  <?php endif; ?>

                  <img class="d-block w-100" style="<?= $lp->style ?? ""; ?>" src="<?= get_image_link($lp->data->image); ?>" alt="<?= lang('sf_single_image'); ?>" />

                </div>
              </div>

            </div>
          </section>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($lp->type == 'description') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Description Section  -->

          <section id="section_<?= $value[0]; ?>" class="lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;">
            <div class="<?= $lp->data->container ?? ""; ?>">

              <div class="row justify-content-md-center">

                <div class="col-md-6 mt-1">
                  <div class="card shadow">
                    <div class="card-body text-center">

                      <?= replace_price($lp->data->content, $pricing, $pricingx, $symbol, $lp_lang); ?>

                      <?php if (isset($lp->data->button_text)) : ?>

                        <div class="mt-3">
                          <a href="<?= $lp->data->button_link; ?>" area-label="<?= isset($lp->data->button_text) ? $lp->data->button_text : lang('sf_description_button'); ?>" class="btn btn-primary btn-block animate__animated <?= $lp->data->button_animation; ?> animate__infinite AddToCart" style="border-radius:<?= $lp->data->border_radius; ?> ;<?= $lp->style ?>">
                            <?= replace_price($lp->data->button_text, $pricing, $pricingx, $symbol, $lp_lang); ?>
                          </a>
                        </div>

                      <?php endif; ?>

                    </div>
                  </div>
                </div>

              </div>

            </div>
          </section>

        <?php endif; ?>
      <?php endif; ?>

      <?php if ($lp->type == 'button') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- CTA Button  -->

          <section id="section_<?= $value[0]; ?>" class="lp_section section_<?= $value[0]; ?>" style="background: <?= $lp->data->background ?? ""; ?> !important;">

            <div class="<?= $lp->data->container ?? ""; ?>">

              <div class="row justify-content-md-center">

                <?php if (($lp->data->sticky ?? "") == 'top') : ?>

                  <style>
                    body {
                      padding-top: 60px;
                    }
                  </style>

                <?php elseif (($lp->data->sticky ?? "") == 'bottom') : ?>

                  <style>
                    body {
                      padding-bottom: 60px;
                    }
                  </style>

                <?php endif; ?>

                <div class="col-md-6">

                  <div class="sticky_<?= $lp->data->sticky ?? ''; ?>">
                    <a href="<?= $lp->data->button_link; ?>" area-label="<?= isset($lp->data->button_text) ? $lp->data->button_text : lang('sf_description_button'); ?>" class="btn btn-primary btn-block animate__animated <?= $lp->data->button_animation; ?> animate__infinite AddToCart" style="<?= $lp->style ?>">
                      <?= replace_price($lp->data->button_text, $pricing, $pricingx, $symbol, $lp_lang); ?>
                    </a>
                  </div>

                </div>

              </div>

            </div>

          </section>

        <?php endif; ?>

      <?php endif; ?>


      <?php if ($lp->type == 'express') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Express Checkout Form  -->
          <section id="section_<?= $value[0]; ?>" class="lp_section section_<?= $value[0]; ?>" style="background: <?= $lp->data->background ?? ""; ?> !important;">

            <div class="<?= $lp->data->container ?? ""; ?>">

              <div class="row justify-content-md-center">

                <div class="col-md-6 justify-content-center">

                  <div class="card shadow border-0 mt-1">

                    <?php if (isset($lp->data->title)) : ?>

                      <div class="mb-0 py-3 px-3">
                        <h3 class="font-weight-medium mb-0 f-15">
                          <?= replace_price($lp->data->title, $pricing, $pricingx, $symbol, $lp_lang);  ?>
                        </h3>
                      </div>

                    <?php endif; ?>

                    <div class="pb-0 mb-3 mt-0" style="border-bottom:0.6px dashed #ffffff75;"></div>

                    <form role="form" data-type="myform" class="buynow-class" id="buynow" action="<?= $store_link . '/lp/' . $lpinfo[0]['LP_SKU']; ?>?type=success" method="POST">

                      <input type="hidden" name="lpsku" value=<?= $id_path ?> />

                      <div class="card-body pb-2 pt-0 px-3 text-<?= $lp_lang == 'Arabic' ? 'right' : 'left'; ?>">

                        <div class="form-row">

                          <?php $variants = json_decode($pro_info[0]['Variants'], TRUE) ?? []; ?>

                          <?php if (count($variants) > 0) : ?>
                            <div class="product-section single-variants col-md-12">

                              <div class="single-variant">

                                <?php
                                foreach ($variants as $variant_key => $value) :
                                  $type_v = $value["type"] ?? "dropdown";
                                ?>

                                  <p class="option-name f-14"><?php echo $value['option']; ?></p>

                                  <div class="textual-buttons-container mb-2 row">

                                    <?php $opt = explode(",", $value['values']); ?>
                                    <?php if ($type_v == "dropdown") : ?>
                                      <div class="input-group input-group-alternative  mb-3">
                                        <select class="form-control variants_input" name="variants[<?= bin2hex($value['option']); ?>]">
                                          <?php foreach ($opt as $i => $k) : ?>
                                            <option class="variants_input" value="<?php echo $k; ?>"><?php echo $k; ?></option>
                                          <?php endforeach; ?>
                                        </select>
                                      </div>
                                    <?php else : ?>
                                      <?php
                                      foreach ($opt as $i => $k) :
                                        $colors = $value["colors"] ?? [];
                                        $found_key = array_search($k, array_column($colors, 'name'));
                                        $color = $colors[$found_key]["color"] ?? "";
                                      ?>
                                        <?php if ($type_v == "color") : ?>
                                          <div class="textual-button">
                                            <input type="radio" class="custom-control-input variants_input" name="variants[<?= bin2hex($value['option']); ?>]" id="variant_<?= $k, $i; ?>_<?=$key?>" value="<?php echo $k; ?>" <?= $i == 0 ? 'checked' : false; ?>/>
                                            <label for="variant_<?= $k, $i; ?>_<?=$key?>" class="color-choice" style="background:<?= $color ?>"></label>
                                          </div>
                                        <?php elseif ($type_v == "radio") : ?>
                                          <div class="d-block my-3 custom-control custom-radio">
                                            <input type="radio" class="variants_input custom-control-input"
                                             name="variants[<?= bin2hex(($value['option'])); ?>]" id="variant_<?= $k, $i; ?>_<?=$key?>" value="<?php echo $k; ?>" <?= $i == 0 ? 'checked' : false; ?> />
                                          <label class="radio-variant d-flex align-items-center custom-control-label mr-2" for="variant_<?= $k, $i; ?>_<?=$key?>">
                                            <?php echo $k; ?>
                                          </label>
                                          </div>
                                        <?php endif; ?>
                                      <?php endforeach; ?>
                                    <?php endif; ?>

                                  </div>

                                <?php endforeach; ?>

                              </div>

                            </div>

                          <?php endif; ?>

                          <?php if (($lp->data->offers ?? '') == 'active' && $list_offers->count() > 0) : 
                            $skippOffers = false;
                            ?>
                            <div class="col-md-12 mb-3 display_offers" id="display_offers" >
                              <label for="offers" class="f-14"> <?= lang('sf_chooseuroffer'); ?> </label>
                              <div class="offers_list<?= $lp->data->offers_type == 'radio' ? '' : ' input-group input-group-alternative' ?>" id="offers_list" data-type="<?= $lp->data->offers_type == 'radio' ? 1 : 2 ?>" data-message="<?= $lp->data->offers_message?>">
                              </div>
                            </div>

                          <?php elseif (($lp->data->offers ?? '') == 'qty') : ?>

                            <link rel="stylesheet" href="<?= $link ?>/assets_landing/css/qty.css" />

                            <div class="form-group col-md-12">

                              <div class="cart-plus-minus">
                                <label for="qtyChange" class="f-14"> <?= lang('quantity'); ?> </label>
                                <input type="text" id="qtyChange" name="offers" class="in-num border_inputs form-control qtyChange" value="1" min="1" max="100" readonly="">
                                <div class="dec qtybutton">-</div>
                                <div class="inc qtybutton">+</div>
                              </div>
                              <input type="hidden" name="skipOffers" value="1">

                              <div class="qty_error mt-3" style="display: none;">
                                <span class="text-red f-13"> <?= lang('Quantity_error'); ?> </span>
                              </div>

                            </div>

                          <?php endif; ?>

                          <div class="form-group col-<?= !isset($lp->data->show_address_input) || $lp->data->show_address_input != 'Inactive' ? '6' : '12'; ?> mb-3">
                            <label for="name" class="f-14"><?= lang('sf_urfunllname'); ?></label>
                            <div class="input-group input-group-alternative">
                              <input class="form-control name" name="fname" id="name" type="text" required>
                            </div>

                            <div class="errorForbiddenKeyword_name mt-2" style="display: none;">
                              <span class="text-red f-13"> <?= lang('sf_namennotvalid'); ?> </span>
                            </div>

                          </div>


                          <?php if (!isset($lp->data->show_address_input) || $lp->data->show_address_input != 'Inactive') : ?>

                            <div class="form-group col-6 mb-3">
                              <label for="address" class="f-14"><?= lang('sf_fulladdress'); ?> </label>
                              <div class="input-group input-group-alternative">
                                <input class="form-control address" name="address" id="address" type="text" required>
                              </div>

                              <div class="errorForbiddenKeyword_address mt-2" style="display: none;">
                                <span class="text-red f-13"> <?= lang('sf_addnotvalid'); ?> </span>
                              </div>

                            </div>

                          <?php endif; ?>


                          <?php if (!isset($lp->data->show_city_input) || $lp->data->show_city_input != 'Inactive') : ?>

                            <div class="form-group col-6 mb-3">
                              <label for="city" class="f-14"><?= lang('sf_urcity'); ?></label>
                              <div class="input-group input-group-alternative">
                                <input class="form-control city" id="city" name="city" type="text" required>
                              </div>
                              <div class="errorForbiddenKeyword_city mt-2" style="display: none;">
                                <span class="text-red f-13"> <?= lang('sf_citynotvalid'); ?> </span>
                              </div>
                            </div>


                          <?php endif; ?>

                          <div class="form-group col-<?= !isset($lp->data->show_city_input) || $lp->data->show_city_input != 'Inactive' ? '6' : '12'; ?> mb-3">

                            <label for="phoneNumber" class="f-14"><?= lang('sf_urphone'); ?></label>

                            <div class="input-group input-group-alternative">
                              <?php if (!isset($lp->data->show_flag_phone) || $lp->data->show_flag_phone != 'Inactive') : ?>
                                <input type="hidden" name="Country" id="countryField" />
                                <div class="countrypicker"></div>
                              <?php endif; ?>
                              <input class="form-control phoneNumber" dir="ltr" onkeypress="return onlyNumberKey(event)" id="phoneNumber" name="phone" type="text" required>
                            </div>

                            <div class="errorPhone mt-2" style="display: none;">
                              <span class="text-red f-13"> <?= lang('sf_phonenotvalid'); ?></span>
                            </div>

                          </div>

                          <?php if ($lp->data->note_input == 'active') : ?>

                            <div class="form-group mb-3 col-md-12">
                              <label for="note" class="f-14" style="margin-bottom: 15px !important;"><?= lang('sf_note1'); ?> <span class="text-muted f-14"> - <?= lang('sf_note2'); ?> - </span></label>
                              <textarea class="form-control form-control-alternative" name="note" rows="4" placeholder="<?= lang('sf_note3'); ?>"></textarea>
                            </div>
                        </div>

                      <?php endif; ?>

                      <input type="hidden" name="lp_sku_abandoned_checkout" value="<?= $lpid; ?>">
                      <input type="hidden" class="pixel_final_total_price" name="pixel_final_total_price" id="pixel_final_total_price" value="<?= $priceDefault['price']; ?>">

                      <?php if (($lp->data->show_total ?? '') == 'active') : ?>
                        <div class="shop-details-price" style="width:100%;margin-bottom: 0px;">
                          <h2>
                            <span class="price-title"> <?= lang('price'); ?> :</span> &nbsp;
                            <?php
                            echo $symbol;
                            echo '<span class="new-price-variant">' . $priceDefault['price'] . '</span>';
                            ?>

                            &nbsp;
                            &nbsp;
                            <del>
                              <?php
                              echo $symbol;
                              echo '<span class="old-price-variant">' . $priceDefault['compare_price'] . '</span>';
                              ?>
                            </del>
                          </h2>
                        </div>
                      <?php endif; ?>

                      <div class="text-center mt-2 mb-2" style=" width: 100%;">

                        <button class="btn btn-primary btn-lg btn-block smooth BuyRightnow py-3 px-5 mb-3 f-22" name="BuyRightnow" type="button" style="<?= $lp->style ?? ""; ?>" title="<?= lang('sf_buynow'); ?>">
                          <i class="fas fa-long-arrow-alt-<?= $lp_style->Settings[0]->lang == 'Arabic' ? 'left' : 'right'; ?> arrow1 m<?= $lp_lang == 'Arabic' ? 'r' : 'l'; ?>-3" aria-hidden="true"></i> <?= replace_price($lp->data->button_text, $pricing, $pricingx, $symbol, $lp_lang); ?>
                        </button>

                      </div>

                      </div>
                      <input type="hidden" name="from_location_page" value="2" />
                    </form>

                  </div>

                </div>
              </div>
            </div>
          </section>

          <?php
          if (!isset($lp->data->show_flag_phone) || $lp->data->show_flag_phone != 'Inactive') {
            if (!in_array("countrypicker", $scriptSet))
              array_push($scriptSet, "countrypicker");
          }

          if (!in_array("express_checkout", $scriptSet))
            array_push($scriptSet, "express_checkout");
          ?>

        <?php endif; ?>
      <?php endif; ?>


      <?php if ($lp->type == 'reviews') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Reviews Checkout Form  -->

          <section id="section_<?= $value[0]; ?>" style="<?= $lp->style ?? ""; ?>background: <?= $lp->data->background ?? ""; ?> !important;" class="review_prev">

            <div class="<?= isset($lp->data->container) ? $lp->data->container : "container-fluid"; ?>">

              <?php if (($lp->data->type ?? "") == "list") : ?>

                <div class="row justify-content-center">

                  <div class="col-md-6 text-center" style="margin:auto;">

                    <h3 class="text-center mb-3 mt-3"> <?= $lp->data->title ?? ""; ?> </h3>

                    <?php if (isset($lp->data->sub_title)) : ?>
                      <h5> <?= $lp->data->sub_title ?? ""; ?> </h5>
                    <?php endif; ?>

                    <div class="row justify-content-center">
                      <?php $i = 0; ?>
                      <?php if (($lp->data->design_type ?? "") == "user_img") : ?>
                        <div class="col-md-12 text-center">
                          <div class="border-0 mb-3 bg-white">
                            <?php foreach ($lp->list as $k2 => $v2) : ?>

                              <div class="card-body px-2 text-center list_reviews mb-3 section_<?= $value[0]; ?>" style="<?= $lp->style ?? ""; ?>">
                                <div class="testi-avatar-img mb-3">
                                  <?php if (isset($v2->image)) : ?>
                                    <img src="<?= get_image_link($v2->image); ?>" alt="<?= lang('sf_single_image') ?>" class="rounded-circle m-auto mb-3 mt-3" />
                                  <?php endif; ?>
                                </div>

                                <div class="mx-2">

                                  <div class="testi-avatar-info">
                                    <h5><?= $v2->name; ?></h5>
                                    <p class="text-muted"> <?= $v2->city; ?></p>
                                    <div class="rating">
                                      <?= show_reviews($v2->rate); ?>
                                    </div>
                                  </div>

                                  <div class="testi-content">
                                    <p class="text-muted"><?= isset($v2->comment) ? $v2->comment : ''; ?></p>
                                  </div>

                                </div>

                              </div>

                              <?php $i++; ?>

                            <?php endforeach; ?>
                          </div>
                        </div>

                      <?php else : ?>
                        <div class="col-md-12 text-center">
                          <div class="border-0 mb-3 bg-white">
                            <?php foreach ($lp->list as $k2 => $v2) : ?>

                              <div class="card-body px-2 text-center product_review" style="<?= $lp->style ?? ""; ?>">
                                <?php if (isset($v2->image)) : ?>
                                  <img src="<?= get_image_link($v2->image); ?>" alt="<?= lang('sf_preview_product') ?>" class="mb-3 mt-3 rounded" />
                                <?php endif; ?>
                                <div class="product_review_info mb-3">
                                  <h5><?= $v2->name; ?></h5>
                                  <p class="text-muted"> <?= $v2->city; ?></p>
                                  <div class="rating mb-2">
                                    <?= show_reviews($v2->rate); ?>
                                  </div>
                                  <p class="text-muted"><?= isset($v2->comment) ? $v2->comment : ''; ?></p>
                                </div>
                              </div>


                              <?php $i++; ?>

                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>

                  </div>
                </div>
              <?php else : ?>

                <div class="row justify-content-center">

                  <div class="col-md-6  px-0 text-center">
                    <h3 class="text-center mb-3 mt-3"> <?= $lp->data->title ?? ""; ?> </h3>
                    <?php if (isset($lp->data->sub_title)) : ?>
                      <h5> <?= $lp->data->sub_title ?? ""; ?> </h5>
                    <?php endif; ?>
                    <?php if (($lp->data->design_type ?? "") == "user_img") : ?>
                      <div class="testimonial-active testimonial-item" dir="ltr" style="<?= $lp->style ?? ""; ?>" title="<?= lang("sf_testimonial"); ?>">
                        <?php foreach ($lp->list as $k2 => $v2) : ?>
                          <div class="text-center">
                            <div class="testi_all">
                              <div class="testi-avatar-img mb-3">
                                <?php if (isset($v2->image)) : ?>
                                  <img src="<?= get_image_link($v2->image); ?>" alt="<?= lang("sf_testimonial"); ?>" width="72px" height="72px" class="rounded-circle m-auto  mb-3" />
                                <?php endif; ?>
                              </div>

                              <div class="testi-avatar-info">
                                <h5><?= $v2->name; ?></h5>
                                <p class="text-muted" <?= $lp_lang == 'Arabic' ? 'dir=\'rtl\'' : '' ?>><?= $v2->city; ?></p>
                                <div class="rating mb-0">
                                  <?= show_reviews($v2->rate); ?>
                                </div>
                              </div>
                              <div class="testi-content">
                                <p class="text-muted"><?= isset($v2->comment) ? $v2->comment : ''; ?></p>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else : ?>

                      <div class="testimonial-active testimonial-item product_review" dir="ltr" style="<?= $lp->style ?? ""; ?>" itle="<?= lang("sf_testimonial"); ?>">
                        <?php foreach ($lp->list as $k2 => $v2) : ?>

                          <div class="text-center">
                            <div class="testi_all">
                              <?php if (isset($v2->image)) : ?>
                                <img src="<?= get_image_link($v2->image); ?>" alt="<?= lang("sf_preview_product"); ?>" class="mb-3 mt-3 rounded">
                              <?php endif; ?>
                              <div class="product_review_info mb-3">
                                <h5><?= $v2->name; ?></h5>
                                <p class="text-muted"><?= $v2->city; ?> </p>
                                <div class="rating">
                                  <?= show_reviews($v2->rate); ?>
                                </div>
                                <p class="text-muted"><?= $v2->comment; ?></p>
                              </div>
                            </div>
                          </div>

                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

            </div>

            <?php
                if (!in_array("slickjs", $scriptSet))
                  array_push($scriptSet, "slickjs");
            ?>

          <?php endif; ?>

          </section>

        <?php endif; ?>
      <?php endif; ?>


      <?php if ($lp->type == 'warranty') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Warranty Section  -->
          <section id="section_<?= $value[0]; ?>" class="lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;<?= $lp->style ?? ""; ?>">

            <div class="<?= $lp->data->container ?? ""; ?>">

              <div class="row justify-content-md-center">

                <div class="col-md-6 text-center">
                  <?php if (isset($lp->data->sub_title)) : ?>
                    <h5 style="color:<?= $lp->data->color_sub_title  ?? ""; ?> ;font-size:<?= $lp->data->size_sub_title ?? ""; ?> ;"> <?= $lp->data->sub_title ?? ""; ?> </h5>
                  <?php endif; ?>
                  <h4 style="color:<?= $lp->data->color_title ?? ""; ?> ;font-size:<?= $lp->data->size_title ?? ""; ?> ;" class=" text-center mb-4"><?= $lp->data->title ?></h4>
                  <div class="row justify-content-md-center">

                    <?php $i = 0; ?>

                    <?php foreach ($lp->list as $k3 => $v3) : ?>

                      <div class="col-md-12 col-12">

                        <div class="card shadow border-0">

                          <div class="card-body px-3">

                            <div class="text-center">
                              <i class="<?= $v3->icon; ?> mt-1 fa-3x fa-pull-center" style="width: 75px;height: 55px; text-align: center;color:<?= $v3->color; ?> !important;"></i>
                            </div>

                            <div class="text-center">
                              <h4 class="f-16 mb-2" style="font-size: <?= $lp->data->badge_size_title ?? ""; ?>!important;"><?= $v3->title; ?></h4>
                              <p class="mb-0" style="font-size: <?= $lp->data->badge_size_sub_title ?? ""; ?>!important;"><?= $v3->sub_title; ?></p>
                            </div>

                          </div>


                        </div>

                      </div>

                      <?php $i++; ?>

                    <?php endforeach; ?>

                  </div>

                </div>

              </div>

            </div>
          </section>

        <?php endif; ?>
      <?php endif; ?>


      <?php if ($lp->type == 'countdown') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- CountDown Section  -->

          <script src="<?= $cdn_funnel_path ?>/js/countdown.js"></script>

          <section id="section_<?= $value[0]; ?>" class="lp_section" style="background: <?= $lp->data->background ?? ""; ?> !important;">
            <div class="<?= $lp->data->container ?? ""; ?> mb-3">

              <div class="row justify-content-md-center">

                <div class="col-md-6 mb-2 text-center">
                  <div class="card shdaow border-0" style="<?= $lp->style ?? ""; ?>">
                    <div class="card-body" style="<?= $lp->style ?? ""; ?>">
                      <div class="text-white mb-2" style="<?= $lp->style ?? ""; ?>">
                        <?= replace_price($lp->data->title, $pricing, $pricingx, $symbol, $lp_lang); ?>
                        <br><br>
                        <span><?= replace_price($lp->data->sub_title, $pricing, $pricingx, $symbol, $lp_lang); ?></span>
                      </div>
                      <div class='countdown mb-3 text-center' style="<?= $lp->style ?? ""; ?>" data-date="<?= $lp->data->ending_time; ?>" dir="ltr"></div>
                      <div>
                        <?php if (isset($lp->data->text_button)) : ?>
                          <a href="#buynow" class="btn btn-white text-black btn-lg btn-block btn-lg mt-2 animate__animated <?= $lp->data->button_animation; ?> animate__infinite AddToCart" area-label="<?= isset($lp->data->button_text) ? $lp->data->button_text : lang('sf_description_button'); ?>">
                            <?= replace_price($lp->data->text_button, $pricing, $pricingx, $symbol, $lp_lang); ?>
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

            </div>
          </section>

        <?php endif; ?>
      <?php endif; ?>


      <?php if ($lp->type == 'collapse') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <section id="section_<?= $value[0]; ?>" class="collapse_qa section_<?= $value[0]; ?>" style="background: <?= $lp->data->background ?? ""; ?> !important;<?= $lp->style ?? ""; ?>">
            <div class="<?= $lp->data->container ?? ""; ?>">
              <div class="row justify-content-md-center">
                <div class="col-md-6 text-center">
                  <?php if (isset($lp->data->title)) : ?>

                    <h4 class="text-center mb-3">
                      <?= $lp->data->title; ?>
                    </h4>
                  <?php endif; ?>

                  <?php if (isset($lp->data->sub_title)) : ?>
                    <h5 class="mb-4"> <?= $lp->data->sub_title ?? ""; ?> </h5>
                  <?php endif; ?>

                  <div class="accordion" id="accordionExample">

                    <?php foreach ($lp->list as $k => $cl) : ?>

                      <div class="card mb-2">
                        <div class="card-header collapsed" id="headingOne{{ @index }}" data-toggle="collapse" data-target="#collapseOne<?= $k; ?>" aria-controls="collapseOne<?= $k; ?>">
                          <h5 class="d-flex justify-content-between align-item-center mb-0"> <?php echo $cl->q; ?> <i class="fas fa-angle-down"></i> </h5>
                        </div>
                        <div id="collapseOne<?= $k; ?>" class="collapse" aria-labelledby="headingOne{{ @index }}" data-parent="#accordionExample">
                          <div class="card-body py-3">
                            <p class="mb-0 mx-3"> <?php echo $cl->a; ?> </p>
                          </div>
                        </div>
                      </div>

                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </section>

        <?php endif; ?>
      <?php endif; ?>


      <?php if ($lp->type == 'logo') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <section id="section_<?= $value[0]; ?>" class="section_<?= $value[0]; ?>" style="background: <?= $lp->data->background ?? ""; ?>">
            <div class="{{ data.container }}" style="<?= $lp->style ?? ""; ?>">

              <img src="<?= get_image_link($lp->data->image); ?>" width="<?= $lp->data->width ?? ""; ?>" height="<?= $lp->data->height ?? ""; ?>" alt="<?= lang('sf_logo_image') ?>" />

            </div>

          </section>

        <?php endif; ?>
      <?php endif; ?>

      <?php if ($lp->type == 'whatsapp') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <section id="section_<?= $value[0]; ?>" class="section_<?= $value[0]; ?>">
            <div class="whatssap_button_box  <?php if ($lp->data->position == 'left') : ?> whatssap_button_left <?php else : ?> whatssap_button_right <?php endif; ?>">
              <a area-label="loble" class="whatssap_button" style="<?= $lp->style ?? ""; ?>" href="https://api.whatsapp.com/send?phone=<?= $lp->data->phone ?? ""; ?>&text=<?= $lp->data->message ?? ""; ?>" target="_blank" area-label="whatsapp btn">
                <i class="fab fa-whatsapp"></i>
              </a>
            </div>

          </section>

        <?php endif; ?>
      <?php endif; ?>

      <?php if ($lp->type == 'footer') : ?>
        <?php if (!isset($lp->data->d_section) || $lp->data->d_section != 'inactive') : ?>
          <!-- Footer Section  -->

          <footer id="section_<?= $value[0]; ?>" class="section_<?= $value[0]; ?>" style="background: <?= $lp->data->background ?? ""; ?>">
            <div class="<?= $lp->data->container ?? ""; ?>">
              <div class="row">
                <div class="col-md-12 mt-0 mb-2">

                  <?php if ($lp->data->social == 'active') : ?>

                    <div class="text-center mt-3 mb-3">

                      <?php if (isset($store_settings[0]['facebook_link'])) : ?>

                        <a href="<?= $store_settings[0]['facebook_link']; ?>" target="_blank" aria-label="<?= lang('sf_facebook_label') ?>">
                          <button type="button" class="btn rounded-circle btn-icon-only mx-1" style="<?= $lp->style ?? ""; ?>" title="<?= lang('sf_facebook_label'); ?>">
                            <span class="btn-inner--icon"><i class="fab fa-facebook fa-fw" style="padding-top: 6px;"></i></span>
                          </button>
                        </a>

                      <?php endif; ?>


                      <?php if (isset($store_settings[0]['instagram_link'])) : ?>

                        <a href="<?= $store_settings[0]['instagram_link']; ?>" target="_blank" aria-label="<?= lang('sf_insta_label') ?>">
                          <button type="button" class="btn rounded-circle btn-icon-only mx-1" style="<?= $lp->style ?? ""; ?>" title="<?= lang('sf_insta_label'); ?>">
                            <span class="btn-inner--icon"><i class="fab fa-instagram fa-fw" style="padding-top: 6px;"></i></span>
                          </button>
                        </a>

                      <?php endif; ?>


                      <?php if (isset($store_settings[0]['tiktok_link'])) : ?>

                        <a href="<?= $store_settings[0]['tiktok_link']; ?>" target="_blank" aria-label="<?= lang('sf_tiktok_label') ?>">
                          <button type="button" class="btn rounded-circle btn-icon-only mx-1" style="<?= $lp->style ?? ""; ?>" title="<?= lang('sf_tiktok_label'); ?>">
                            <span class="btn-inner--icon"><i class="fab fa-tiktok fa-fw" style="padding-top: 6px;"></i></span>
                          </button>
                        </a>

                      <?php endif; ?>


                      <?php if (isset($store_settings[0]['twitter_link'])) : ?>

                        <a href="<?= $store_settings[0]['twitter_link']; ?>" target="_blank" aria-label="<?= lang('sf_twitter_label') ?>">
                          <button type="button" class="btn rounded-circle btn-icon-only mx-1" style="<?= $lp->style ?? ""; ?>" title="<?= lang('sf_twitter_label'); ?>">
                            <span class="btn-inner--icon"><i class="fab fa-twitter fa-fw" style="padding-top: 6px;"></i></span>
                          </button>
                        </a>

                      <?php endif; ?>

                      <?php if (isset($store_settings[0]['snapchat_link'])) : ?>

                        <a href="<?= $store_settings[0]['snapchat_link']; ?>" target="_blank" aria-label="<?= lang('sf_snapchat_button'); ?>">
                          <button type="button" class="btn rounded-circle btn-icon-only mx-1" style="<?= $lp->style ?? ""; ?>" title="<?= lang('sf_snapchat_button'); ?>">
                            <span class="btn-inner--icon"><i class="fab fa-snapchat fa-fw" style="padding-top: 6px;"></i></span>
                          </button>
                        </a>

                      <?php endif; ?>

                    </div>

                  <?php endif; ?>

                  <?php if ($lp->data->email == 'active') : ?>

                    <div class="text-center mt-2">
                      <span class="text-black"><?= lang('sf_email'); ?> :</span>
                      <a href="mailto:<?= $store_info[0]['business_email']; ?>" aria-label="<?= lang('sf_send_mail'); ?>">
                        <?= $store_info[0]['business_email']; ?>
                      </a>
                    </div>

                  <?php endif; ?>

                  <?php if (isset($lp->data->title)) : ?>

                    <div class="pb-2 mb-3 mt-2" style="border-bottom:0.6px dashed #0000002e;"></div>

                    <div class="text-center m-auto" style="font-size:15px;padding-bottom: 0rem!important;">
                      <span class="mt-4"> <?= $lp->data->title; ?> </span>
                    </div>

                  <?php endif; ?>

                </div>
              </div>
            </div>
          </footer>

        <?php endif; ?>
      <?php endif; ?>



    <?php endforeach; ?>


  </div>

  <?php if (in_array("slickjs", $scriptSet)) :  ?>
    <!-- -->
    <link rel="stylesheet" href="<?= $cdn_funnel_path ?>/css/slick.min.css">
    <script src="<?= $cdn_funnel_path ?>/cdn_funnels_example/slick.min.js"></script>
    <script>
      $(".testimonial-active").slick({
        dots: true,
        infinite: true,
        speed: 1000,
        centerMode: true,
        arrows: false,
        slidesToShow: 1,
        rtl: true
      });
    </script>

  <?php endif; ?>

  <?php if (in_array("countrypicker", $scriptSet)) :  ?>
    <script type="text/javascript">
      var countries = <?= json_encode($countries_list); ?>;
      var link_flags = "<?= cdn_link; ?>/dropify_funnels/flags/";
      var country = "<?= $data_w->GET_COUNTRY_INFO('country_code') ?>";
    </script>
    <script src="<?= $cdn_funnel_path; ?>/cdn_funnels_example/js/countrypicker.js?v=<?= UPDATE_VERSION; ?>"></script>
  <?php endif; ?>


  <script src="<?= $link; ?>/assets/js/event.js"></script>

 
  <script>
    eventViewContent();
  </script>
 
  <?php if (in_array("express_checkout", $scriptSet)) :  ?>
      
    <div class="modal fade" id="confirmation-datepciker" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

      <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

          <div class="modal-body pt-4 pb-0">


            <div class="form-row">

              <div class="form-group col-md-12 mb-2 text-center">
                <h2 class="mb-3"> <?= $time2call_title ?> </h2>
                <h4 style="line-height: 33px;font-weight: 400 !important;"><?= $time2call_message; ?></h4>
              </div>

              <div class="form-group col-md-12 mb-3">
                <label for="time2call" class="text-left f-14"><?= lang('sf_success8'); ?> </label>
                <div class="input-group input-group-alternative">

                  <select class="form-control" name="time2call" id="time2call" required="">
                    <option value="" selected>-- <?= lang('sf_packatime'); ?> --</option>
                    <option><?= lang('sf_fromto1'); ?></option>
                    <option><?= lang('sf_fromto2'); ?></option>
                    <option><?= lang('sf_fromto3'); ?></option>
                    <option><?= lang('sf_fromto4'); ?></option>
                  </select>
                </div>
                <div class="time2call_error mt-2" style="display: none;">
                  <span class="text-red f-13"> <?= lang('sf_phonenotvalid'); ?></span>
                </div>


              </div>

              <?php if (isAppActive(11) && count($fetch_gmap_info) > 0 && $lpinfo[0]['checkout_gmap'] && isset($applicationGoogleMapApiKey)) : ?>

                <?php if ($fetch_gmap_info[0]['display_on'] == 'both' || $fetch_gmap_info[0]['display_on'] == 'only_funnels') : ?>

                  <style>
                    /* To hode the map helper  */
                    .gm-style-cc {
                      display: none !important;
                    }
                  </style>

                  <div class="form-group col-md-12 mb-3">
                    <div class="form-grp mb-3">
                      <label for="map-area-box"> <?= lang('map_helper_title') ?> <small>(<?= lang('optional'); ?>)</small></label>
                      <small class="text-muted d-block "> <?= lang('map_helper_info') ?> </small>
                    </div>
                    <div id="map-area-box" class="rounded-lg shadow bg-secondary position-relative" style="display:block;height:200px">
                      <div class="position-absolute " style="width:90%;top: 50%!important; left: 50%!important;transform: translate(-50%,-50%)!important;" id="map-area-box-callback"> <?= lang('map_loading') ?> </div>
                    </div>
                    <input type="hidden" name="location_latitude" id="map-latitude-position">
                    <input type="hidden" name="location_longitude" id="map-longitude-position">
                  </div>

                <?php endif; ?>

              <?php endif; ?>

              <div class="form-group col-md-12 mb-0">
                <label for="changeNumber" style="font-size:14px;"> <?= lang('sf_success6'); ?> </label>
                <div class="input-group input-group-alternative mb-2">
                  <input class="form-control" placeholder="<?= lang('sf_success5'); ?>" type="text" id="changeNumber" name="changeNumber" value="<?= $phone; ?>" disabled required dir="ltr">
                  <span class="input-group-addon input-group-append">
                    <button title="<?= lang('sf_success5'); ?>" class="btn btn-black" type="button" id="ChangePhone" style="border-radius: <?= $lp_lang == 'Arabic' ? '0px 4px 4px 0px' : '4px 0px 0px 4px'; ?> !important;">
                      <i class="fas fa-pen-alt"></i>
                    </button>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer pt-2">
            <button type="submit" class="btn btn-black f-14 btn-block" id="confirmdata_btn" onclick="confirmdate()" title="<?= lang('sf_success4'); ?>">
              <i class="fas fa-check fa-fw mx-1"></i> <?= lang('sf_success4'); ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <?php if (isAppActive(11) && count($fetch_gmap_info) > 0 && $lpinfo[0]['checkout_gmap']  && isset($applicationGoogleMapApiKey)) : ?>

      <?php if ($fetch_gmap_info[0]['display_on'] == 'both' || $fetch_gmap_info[0]['display_on'] == 'only_funnels') : ?>

        <script src="https://maps.googleapis.com/maps/api/js?key=<?= $applicationGoogleMapApiKey; ?>&callback=initMap&v=weekly" async></script>

        <script type="text/javascript">
          var map_retry = "<?= lang('map_retry'); ?>";
          var map_permission_denied_message = "<?= lang('map_permission_denied_message'); ?>";
          var map_geolocation_not_supported = "<?= lang('map_geolocation_not_supported'); ?>";
        </script>

        <script src="<?= cdn_link; ?>/dropify_funnels/js/gmap.js"></script>

      <?php endif; ?>

    <?php endif; ?>


    <script>
      $(document).ready(function() {
        $('#ChangePhone').click(function() {
          $('#changeNumber').removeAttr('disabled');
        });
        /* $('#confirmation-datepciker').modal('show'); */
      });

      function confirmdate() {

        var time2call = $('#time2call').val();
        if (time2call == '') return false;

        let clickedButton = $('#confirmdata_btn');
        let id = clickedButton.attr('data-output');

        var phone = $('#changeNumber').val();

        clickedButton.append('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        <?php if (isAppActive(11) && count($fetch_gmap_info) > 0 && $lpinfo[0]['checkout_gmap']  && isset($applicationGoogleMapApiKey)) : ?>

          var latlng_data = {
            latitude: inputMapLat.value,
            longitude: inputMapLng.value
          };

        <?php else : ?>

          var latlng_data = {
            latitude: null,
            longitude: null
          };

        <?php endif; ?>


        $.ajax({
          url: base_url + "/ajax/save_time_to_call",
          type: 'post',
          dataType: "json",
          data: {
            id: id,
            phone_edited: phone,
            time2call: time2call,
            latlng: latlng_data
          },
          success: function(response) {
            if (response.status) {
              $('#confirmation-datepciker').modal('hide');
              Swal.fire({
                title: alright,
                text: success_order + ' ' + phone,
                icon: 'success',
                type: 'success',
                buttonsStyling: !1,
                confirmButtonText: '<?= lang('sf_success3'); ?>',
                confirmButtonClass: "btn btn-black"
              });
            } else {
              Swal.fire({
                title: error,
                text: response.message,
                icon: 'warning',
                type: 'warning',
                buttonsStyling: !1,
                confirmButtonText: ok,
                confirmButtonClass: "btn btn-warning"
              })
            }
            $('#confirmdata_btn .fa-spinner').remove();
            clickedButton.prop('disabled', false);
          }
        });

        return false;
      }
    </script>

    <script type="text/javascript">


      function fire_eventPurchais() {

        var price = $('#pixel_final_total_price').val();

        // fire pixels events
        eventPurchais(price, "<?= $er_getVisitorCurrencySymbolVal; ?>");
      }
      <?php

      if (!isset($_SESSION["PUR_$lpid"]) || $_SESSION["PUR_$lpid"] != true) {
        add_view_record($lpid, $lpinfo[0]['Productid'], 'PUR', 'funnels');
      }
      ?>
    </script>

     <script src="<?= $link; ?>/assets/js/handle_express_checkout-prices.js"></script>

  <?php endif; ?>

      <!-- skipp offers -->
    <?php 
        if($skippOffers) $offers_data = [];
    ?>
    
  <script type="text/javascript">
    var price = <?= json_encode($priceDefault, JSON_UNESCAPED_UNICODE); ?>;
    var prices = <?= json_encode($prices, JSON_UNESCAPED_UNICODE); ?>;
    var offers = <?= json_encode($offers_data, JSON_UNESCAPED_UNICODE); ?>;
    var check_atc = false;
    var field_required = "<?= lang('field_required'); ?>"

    $('.AddToCart').click(function() {

      <?php if (!isset($_SESSION["ATC_$lpid"]) ?? '' && $_SESSION["ATC_$lpid"] != true) : ?>

        if (!check_atc) {

          eventAddToCart();

          $.ajax({

            url: base_url + "/ajax/add_to_cart_lp",
            type: 'post',
            data: {

              lpid: <?= $lpid; ?>,
              proid: <?= $lpinfo[0]['Productid'] ?>,
              event: 'ATC',

            },

            success: function(response) {
              if (response == 1) {
                check_atc = true;
              }
            }
          });

        }

      <?php endif; ?>

    });
  </script>

  <?php if (isset($lp_style->Settings[0]->js_custom)) : ?>
    <!-- Custom Landing Page Java Script -->

    <script type="text/javascript">
      <?= $lp_style->Settings[0]->js_custom; ?>
    </script>

  <?php endif; ?>

  <script src="<?= $link ?>/assets_landing/js/funnels.js"></script>

  <script>
    function phonenumber(inputtxt) {
      return true;
    }
     
    function onlyNumberKey(evt) {
      var ASCIICode = (evt.which) ? evt.which : evt.keyCode
      if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
        return false;
      return true;
    }
  </script>

<?php else : ?>

  <style type="text/css">
    body {
      background-color: #fcfcfd !important;
      direction: ltr;
    }
  </style>
  </head>

  <div class="container-fluid">

    <div class="row justify-content-center">

      <div class="col-md-6 py-6 text-center">

        <div><img src="<?= $cdn_funnel_path; ?>/cdn_funnels_example/info.gif" alt="<?= lang('sf_not_active'); ?>"></div>

        <h4><?= lang('sf_not_active'); ?> </h4>

      </div>

    </div>

  </div>

<?php endif; ?>

</body>

</html>
