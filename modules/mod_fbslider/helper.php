<?php

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class modFBLikebox {

	function getFBLikebox( $params)   {

	if (trim( $params->get( 'loadjQuery' ) ) == 1)

	{

	?><script src='https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js' type='text/javascript'/></script>

	<?php

	}

	?>

<style type="text/css">

/*<![CDATA[*/

#fbplikebox{display: block;padding: 0;z-index: 99999;position: fixed;}

.fbplbadge {display: block;height: 150px;top: 50%;margin-top: -75px;position: absolute;left: -35px;width: 47px;background-image: url("<?php echo JURI::root();?>modules/mod_fbslider/vertical-right.png");background-repeat: no-repeat;overflow: hidden;-webkit-border-top-left-radius: 8px;-webkit-border-bottom-left-radius: 8px;-moz-border-radius-topleft: 8px;-moz-border-radius-bottomleft: 8px;border-top-left-radius: 8px;border-bottom-left-radius: 8px;}

/*]]>*/

</style>

<script type="text/javascript">

/*<![CDATA[*/

    (function(w2b){

        w2b(document).ready(function(){

            var $dur = "medium"; // Duration of Animation

            w2b("#fbplikebox").css({right: -<?php echo $params->get( 'fbwidth' ); ?>, "top" : <?php echo $params->get( 'fbtop' ); ?> })

            w2b("#fbplikebox").hover(function () {

                w2b(this).stop().animate({

                    right: 0

                }, $dur);

            }, function () {

                w2b(this).stop().animate({

                    right: -<?php echo $params->get( 'fbwidth' ); ?>

                }, $dur);

            });

            w2b("#fbplikebox").show();

        });

    })(jQuery);

/*]]>*/

</script>

<div id="fbplikebox" style="display:none; background-image:url(''">

    <div class="fbplbadge" style="background-image:url('<?php echo JURI::root();?>modules/mod_fbslider/vertical-right.png');"></div>

    <iframe src="http://www.facebook.com/plugins/likebox.php?href=<?php echo $params->get( 'fbpage_url' ); ?>&amp;width=<?php echo $params->get( 'fbwidth' ); ?>&amp;height=<?php echo $params->get( 'fbheight' ); ?>&amp;colorscheme=<?php echo $params->get( 'colorscheme' ); ?>&amp;show_faces=<?php echo $params->get( 'show_faces' ); ?>&amp;border_color=<?php echo $params->get( 'border_color' ); ?>&amp;stream=<?php echo $params->get( 'stream' ); ?>&amp;header=false" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:<?php echo $params->get( 'fbwidth' ); ?>px; height:<?php echo $params->get( 'fbheight' ); ?>px;background:<?php echo $params->get( 'background' ); ?>;" allowtransparency="true"></iframe>


</div>  

	<?php

	}

}?>



