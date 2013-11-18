<?php 
//$option=$_GET['option'];
//$options = get_option($option);
 ?>
#<?php echo $option; ?>container {
    margin: <?php echo $options['holdermar']; ?>;
    float:<?php echo $options['holderfloat']; ?>;
    }
#<?php echo $option; ?> { 
    width: <?php echo $options['width']; ?>px; 
    height: <?php echo $options['height']; ?>px;
    overflow: hidden; 
    position: relative; 
    }
    

    #<?php echo $option; ?> a, #<?php echo $option; ?> a img {
        border: none !important; 
        text-decoration: none !important; 
        outline: none !important;
        } 
        
    #<?php echo $option; ?> h4 {
        color: #<?php echo $options['textColor']; ?> !important;
        margin: 0px !important;padding: 0px !important;
        font-family: <?php echo $options['fontFamily']; ?> !important;
        font-size: <?php echo $options['titleFont']; ?>px !important;}
        
    #<?php echo $option; ?> .cs-title {
        background: #<?php echo $options['bgColor']; ?>;
        color: #<?php echo $options['textColor']; ?>  !important;
        font-family: <?php echo $options['fontFamily']; ?> !important;
        font-size: <?php echo $options['fontSize']; ?>px !important;
        letter-spacing: normal !important;line-height: normal !important;}
        
    #<?php echo $option; ?> .cs-title{ position:absolute;
    <?php switch($options['layout']){
            case 'stripe-top':{
                echo 'top:0px; height: '.($options['height']/2 -50).'px;width: '.($options['width']-$options['borderWidth']+10).'px; padding: 10px 10px 10px 10px;overflow:hidden;';
                break;
            }
            case 'stripe-right':{ $width=$options['width']/3+$options['borderWidth']-10;
                echo 'margin-left: '.($options['width']-$width).'px;top: 0px;width: '.($width).'px; padding: 10px 10px 0px 10px;';
                break;
            }
            case 'stripe-bottom':{
                          echo 'width: '.($options['width']-$options['borderWidth']-10).'px; padding: 10px;'; 
                break;
            }
            case 'stripe-left':{
                echo 'left:0px;top: 0px;width: '.($options['width']/3+$options['borderWidth']-10).'px; padding: 10px 10px 0px 10px;';
                break;
            }
        }
        ?>
        }
    <?php 
    if($options['buttons'] == 'false')
    {
        echo '#cs-buttons-'.$option.' { display: none; }';
    } 
     ?>
    #<?php echo $option; ?>container .cs-buttons {clear:both; font-size: 0px; margin: <?php echo $options['navplace']; ?>; float: left; }
       #cs-button-<?php echo $option; ?>{ z-index:999;outline:none;}
     <?php
     switch ($options['navstyle']){
        case 'nav_small':  { ?>
       #<?php echo $option; ?>container .cs-buttons { font-size: 0px; margin: 8px 0 0 8px;padding: 8px 8px 8px 5px; float: left; 
                                             background: #dfdfdf;
                                              -webkit-border-radius: 5px;
                                              -moz-border-radius: 5px;
                                              border-radius: 5px;
                                              outline: none !important;
                                            }
                              #<?php echo $option; ?>container .cs-buttons a { margin-left: 5px; height: 5px; width: 5px; float: left; 
                                               background: #<?php echo $options['bgColor']; ?>;
                                               text-indent: -1000px;
                                               -webkit-border-radius: 5px;
                                                -moz-border-radius: 5px;
                                                border-radius: 5px;
                                                outline: none !important;
                                                <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?>
                                                }              
                             #<?php echo $option; ?>container   a.cs-active { background-color: #<?php echo $options['textColor']; ?>; outline: none !important;}          
            <?php break;
        }
        case 'nav_style1':  { ?>
                            #<?php echo $option; ?>container   .cs-buttons a { margin-left: 5px; height: 16px; width: 15px; float: left; 
                                               text-indent: -999px;
                                               background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style1.png') no-repeat;
                                               background-position: left;
                                               outline: none !important;
                                               <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?>
                                               }             
                              #<?php echo $option; ?>container   .cs-buttons a:hover,
 #<?php echo $option; ?>container a.cs-active { background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style1.png') no-repeat;background-position: right; outline: none !important;}          
            <?php break;
        }
        
        case 'nav_style2':  { ?>
                             #<?php echo $option; ?>container  .cs-buttons a { margin-left: 5px; height: 15px; width: 15px; float: left; 
                                               text-indent: -999px; background: #dfdfdf;
                                               border: 5px solid #c6c6c6; 
                                               text-indent: -1000px; 
                                               outline: none !important;
                                               opacity:0.7;filter:alpha(opacity=70);
                                                -webkit-border-radius: 15px;
                                                -moz-border-radius: 15px;
                                                border-radius: 15px;
                                               <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?>
                                               }             
                                 
                              #<?php echo $option; ?>container   .cs-buttons a:hover  { background: #efefef; border-color: #444;outline: none !important;}
                              #<?php echo $option; ?>container   a.cs-active { background: #efefef; border-color: #444; outline: none !important;}          
            <?php break;
        }
        
        case 'nav_style3':  { ?>
                              #<?php echo $option; ?>container .cs-buttons a { margin-left: 5px; height: 33px; width: 33px; float: left; 
                                               text-indent: -999px;
                                               background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_2.png') no-repeat;
                                               outline: none !important;
                                              <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?> 
                                               }             
                               #<?php echo $option; ?>container  .cs-buttons a:hover,
 #<?php echo $option; ?>container a.cs-active { background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_2_active.png') no-repeat; outline: none !important;}          
            <?php break;
        }
        
        case 'nav_style4':  { ?>
                             #<?php echo $option; ?>container  .cs-buttons a { margin-left: 5px; height: 12px; width: 12px; float: left; 
                                               background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style4.png') no-repeat;
                                               background-position: left;
                                               outline: none !important;
                                              <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?>
                                               }             
                              #<?php echo $option; ?>container   .cs-buttons a:hover,
 #<?php echo $option; ?>container a.cs-active { background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style4.png') no-repeat;background-position: right; outline: none !important;}          
            <?php break;
        }
        case 'nav_style5':  { ?>
                              #<?php echo $option; ?>container .cs-buttons a { margin-left: 5px; height: 14px; width: 14px; float: left; 
                                               background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style5.png') no-repeat;
                                               background-position: top;
                                               outline: none !important;
                                              <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?>
                                               }             
                               #<?php echo $option; ?>container .cs-buttons a:hover,
 #<?php echo $option; ?>container a.cs-active { background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style5.png') no-repeat;background-position: bottom; outline: none !important;}          
            <?php break;
        }
        
        default: { ?>
           #<?php echo $option; ?>container .cs-buttons { font-size: 0px; padding: 10px; float: left; outline: none !important;}
           #<?php echo $option; ?>container .cs-buttons a { margin-left: 5px; height: 15px; width: 15px; float: left; 
                            background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/default_style.png') no-repeat;background-position:top;
                                                text-indent: -1000px;
                                                outline: none !important;
                            <?php if($options['vnavenable'])
                                                {
                                                    echo "clear: both;margin-bottom:5px;";
                                                }  ?> }
             #<?php echo $option; ?>container .cs-buttons a:hover  { background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/default_style.png') no-repeat;background-position: bottom;top:15px;outline: none !important;}
            #<?php echo $option; ?>container  a.cs-active { background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/default_style.png') no-repeat;background-position:bottom;outline: none !important;}          
                                
        
            <?php
        }
     }
     ?>
     #<?php echo $option; ?>  .cs-prev,#<?php echo $option; ?>  .cs-next { outline:none; }
     <?php
switch($options['arrstyle']){
    case 'arr_style1':{ ?>
      #<?php echo $option; ?>  .cs-prev {margin-left:8px; line-height: 50px;width: 50px;height:50px; background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style1_arrows-prev.png')no-repeat; text-indent: -999px;}
      #<?php echo $option; ?>  .cs-next {margin-right: 5px; line-height: 50px;width: 50px;height:50px; background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style1_arrows-next.png')no-repeat; text-indent: -999px;}
        <?php break;
    }
    case 'arr_style2':{?>
      #<?php echo $option; ?>  .cs-prev {margin-left:8px; line-height: 30px;width: 30px;height:30px; background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style2_arrows-prev.png')no-repeat; text-indent: -999px;}
      #<?php echo $option; ?>  .cs-next {margin-right: 5px; line-height: 30px;width: 30px;height:30px; background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style2_arrows-next.png')no-repeat; text-indent: -999px;}
    <?php       
        break;
    }
    case 'arr_style3':{ ?>
          #<?php echo $option; ?>  .cs-prev {margin-left:8px; line-height: 50px;width: 50px;height:50px; background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style3_arrows-prev.png')no-repeat; text-indent: -999px;}
          #<?php echo $option; ?>  .cs-next {margin-right: 5px; line-height: 50px;width: 50px;height:50px; background: url('<?php echo WP_CONTENT_URL;?>/plugins/vslider/images/nav_style3_arrows-next.png')no-repeat; text-indent: -999px;}
    <?php
        break;
    }
    default:{?>
           #<?php echo $option; ?>  .cs-prev,#<?php echo $option; ?> .cs-next {font-weight: bold;background: #<?php echo $options['bgColor']; ?> !important;font-size: 28px !important;font-family: "Courier New", Courier, monospace;color: #<?php echo $options['textColor']; ?> 
!important;padding: 0px 10px !important;-moz-border-radius: 5px;-khtml-border-radius: 5px;-webkit-border-radius: 5px;}
    <?php }
}
     ?>
     
       #<?php echo $option; ?>,#<?php echo $option; ?> img {
        border:<?php echo $options['borderWidth']; ?>px solid #<?php echo $options['borderColor']; ?>; 
        border-radius:<?php echo $options['borderRadius']; ?>px;
        -moz-border-radius:<?php echo $options['borderRadius']; ?>px;
        -webkit-border-radius:<?php echo $options['borderRadius']; ?>px;
        }