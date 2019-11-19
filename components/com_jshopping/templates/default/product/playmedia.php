<?php 
/**
* @version      4.12.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
?>
<html>
	<head>
		<title><?php print $this->description; ?></title>
        <?php print $this->scripts_load?>
	</head>
	<body style = "padding: 0px; margin: 0px;">
        <?php if ($this->config->video_html5 && $this->file_is_video){?>
            <div class="file_demo_video">
                <video width="<?php print $this->config->video_product_width; ?>" height="<?php print $this->config->video_product_height; ?>" controls autoplay id = "video">
                    <source 
                        src="<?php print $this->config->demo_product_live_path.'/'.$this->filename;?>" 
                        <?php if ($this->config->video_html5_type){?>
                        type='<?php print $this->config->video_html5_type?>' 
                        <?php }?>
                    />
                </video>
            </div>
		<?php }elseif ($this->config->video_html5 && $this->file_is_audio){?>
            <div class="file_demo_audio">
                <audio controls autoplay>
                    <source 
                        src="<?php print $this->config->demo_product_live_path.'/'.$this->filename;?>" 
                        <?php if ($this->config->audio_html5_type){?>
                        type='<?php print $this->config->audio_html5_type?>' 
                        <?php }?>
                    />
                </audio>
            </div>
        <?php }else{?>
            <a class = "video_full" id = "video" href = "<?php print $this->config->demo_product_live_path.'/'.$this->filename; ?>"></a>
            <script type="text/javascript">
                var liveurl = '<?php print JURI::root()?>';
                jQuery('#video').media( { width: <?php print $this->config->video_product_width; ?>, height: <?php print $this->config->video_product_height; ?>, autoplay: 1} );
            </script>
        <?php }?>
	</body>
</html>