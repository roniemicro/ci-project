<?php


/**
 * SmartImg - Image manipulation Class
 *
 * @version	1.0
 * @author		Roni Kumar Saha
 * @license		GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://www.helpful-roni.com/projects/SmartIm
 */
 
class SmartImg {
    private $quality			= 80;
    private $cacheDir			= '';
    private $mime				= 'image/png';
    private $size				= null;
    private $src				='';			//Image object
    private $dst				= '';			//Image object
    private $modified			='';			//modified image filename with path
    public $cropratio			= null;
    
    private $maxWidth			= 0;
    private $maxHeight			= 0;    
    
    private $tnWidth			= 0;
    private $tnHeight			= 0;
    
    private $offsetX			=0;
	private $offsetY			=0;
	
    //Text to Image Variables
    private $text				='';	
    private $font_file			='';
	private $font_folder		='';
	private $background_color	='#FFFFFF';	
	private $font_color 		= '#000000' ;
	private $font_size  		= 14;
	public  $angel				= 0;

    private $creationFunction	= 'imagecreatefrompng';
	private $outputFunction	 	= 'Imagepng';
	private $doSharpen			= true;
	private $srcPath			= '';			//Source Filename with path
	private $applyed			= '';
	
	public  $nocache			= true;
    public  $cacheDirName		= 'imagecache/';
    public  $memoryToAllocate	= '500M';
    public  $bgcolor			= '';
    public  $outDir				= '';
	public  $SmartMessage 		= false;
	public  $transparent		= false;
	public	$cropOption			= null;
    
/*--------------	Private stuff	-----------------*/

    

/* -------------- Constructor -----------------------*/
	function __construct($path=null){
		$store=realpath(APPPATH."/../../");
		$this->cacheDirName=realpath($store.DIRECTORY_SEPARATOR.$this->cacheDirName).DIRECTORY_SEPARATOR;
		$this->cacheDir=$this->cacheDirName;
		ini_set('memory_limit', $this->memoryToAllocate);
		if($path==null)							//Happend when text 2 image use
			return ;
        $path=$path['path'];
		$this->isImage($path);					//Check if image exist
		$this->srcPath=$path;
		$this->cacheDir=$this->cacheDirName;
		$this->InitOutput();					//Initialize input/output function and type of image
		$creationFunction=$this->creationFunction;
		$this->src	= $creationFunction($path); // Read in the original image
        return $this;
		//return $this->src;
	}


/*--------------    GETTERS & SETTERS    -----------------*/
	function get_value($key_name){	
   	  		return $this->$key_name;
	}
	
	function set_value($key_name,$key_value){
		$this->$key_name=$key_value;
	}
	
    function memoryAllocate($value=null){
    	if($value==null)
    		return $this->memoryToAllocate;
        $this->memoryToAllocate = intval($value);
        ini_set('memory_limit', $this->memoryToAllocate); 
    }
    
    function ImageQuality($value=null){
    	if($value==null)
    		return $this->quality;
        $this->quality = intval($value);

    if (in_array($this->size['mime'], array('image/gif', 'image/png','image/x-png')))
    	$this->quality	= round(10 - ($this->quality / 10)); // PNG needs a compression level of 0 (no compression) through 9
    }
   
     function ImageCacheDir ($path=null){
     	if($path==null)
     		return $this->cacheDir;
        if($path != '')        
        	$this->cacheDir = $path;
    }
    
    function offset($x=null,$y=null){
    	if($x!='' && $x!=null)
    		$this->offsetX=(int) $x;
    	if($y!='' && $y!=null)
    		$this->offsetY=(int) $y;
    }
    
    private function InitOutput(){
    	$this->mime		= $this->size['mime'];
		//echo $this->size['mime'];
		
		switch ($this->size['mime'])
		{
			case 'image/gif':
				// We will be converting GIFs to PNGs to avoid transparency issues when resizing GIFs
				// This is maybe not the ideal solution, but IE6 can suck it
				//echo $this->size['mime'];
				$this->creationFunction	= 'ImageCreateFromGif';
				$this->outputFunction	= 'ImagePng';
				$this->mime				= 'image/png'; // We need to convert GIFs to PNGs
				$doSharpen				= FALSE;
				$this->quality			= NULL; // PNG needs a compression level of 0 (no compression) through 9
				
			break;
			
			case 'image/x-png':
			case 'image/png':
				$this->creationFunction	= 'ImageCreateFromPng';
				$this->outputFunction	= 'ImagePng';
				$this->doSharpen		= FALSE;
				$this->quality			= round(10 - ($this->quality / 10)); // PNG needs a compression level of 0 (no compression) through 9
			break;
			case 'image/bmp' :
				$this->creationFunction	= 'ImageCreateFromBmp';
				$this->outputFunction	= 'ImageJpeg';
				$this->doSharpen		= TRUE;
				break;
			default:
				$this->creationFunction	= 'ImageCreateFromJpeg';
				$this->outputFunction	= 'ImageJpeg';
				$this->doSharpen		= TRUE;
			break;
		}
    }
    
    function findSharp($orig, $final) { // function from Ryan Rud (http://adryrun.com)
		$final	= $final * (750.0 / $orig);
		$a		= 52;
		$b		= -0.27810650887573124;
		$c		= .00047337278106508946;
		$result = $a + $b * $final + $c * $final * $final;
		return max(round($result), 0);
	}

	private function cacheImage(){					//Write to cache directory
		if (!file_exists($this->cacheDirName))
			mkdir($this->cacheDirName, 0755);
		// Make sure we can read and write the cache directory
		if (!is_readable($this->cacheDirName))
			$this->fatal_error("Error: the cache directory is not readable");
		else if (!is_writable($this->cacheDirName))
			$this->fatal_error("Error: the cache directory is not writable");
	}
	

	
	private function InitImage(){
		
	}
	
	private function OutPutCache(){
		$this->_cleanUp();
		header("Content-type: {$this->mime}");
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($this->modified)) . ' GMT');
		header('Content-Length: ' . filesize($this->modified));
		readfile($this->modified);
		exit;
	}
	
	private function CacheFile(){		//Get the cache file name
		$modifiedImageSource		= $this->tnWidth . 'x' . $this->tnHeight .  'x' . $this->maxWidth . 'x' . $this->maxHeight;
		if ($this->text!='')
			$modifiedImageSource	.= 'x' . $this->text;
		if ($this->applyed)
			$modifiedImageSource	.= 'x' . $this->applyed;
		if ($this->srcPath)
			$modifiedImageSource	.= 'x' . $this->srcPath;
		if ($this->bgcolor!='')
			$modifiedImageSource	.= 'x' . $this->bgcolor;
		if ($this->cropratio!=null)
			$modifiedImageSource	.= 'x' . (string) $this->cropratio;
		if ($this->quality!=null)
			$modifiedImageSource	.= 'x' . $this->quality;
		$modifiedImage	= md5($modifiedImageSource);

		$this->modified	= $this->cacheDir . $modifiedImage;
	}
	
	private function OutputOnfly(){		//Output to browser
		$outputFunction=$this->outputFunction;
		$this->CacheFile();				//Get/Generate cache file name
		//echo  $this->modified;
		//exit();
        $imageModified	= filemtime($this->srcPath);
        $gmdate_mod	= gmdate('D, d M Y H:i:s', $imageModified) . " GMT";

        if(!$this->nocache){					//Request for caching
            if(file_exists($this->modified)){			//If the cache file exist
				$thumbModified	= filemtime($this->modified);

                $gmdate_mod	= gmdate('D, d M Y H:i:s', $thumbModified) . " GMT";

                if($imageModified < $thumbModified) {		//thumbnail image is uptodate ie go for chache
                    // Check browser cache
					if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					{
						$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
						if ($if_modified_since >= $gmdate_mod)
						{
							header('HTTP/1.1 304 Not Modified');
							exit();
						}
					}
					
					// Not cached in browser, so we send our cached version
					$this->OutPutCache();
				}
			}
		}
		
		$outputFunction($this->dst, $this->modified, $this->quality);
		//exit();
		header("Content-type: $this->mime");
		header('Last-Modified: ' . $gmdate_mod);
		header('Content-Length: ' . filesize($this->modified));
		$outputFunction($this->dst, null, $this->quality);
		// Clean up the memory
		$this->_cleanUp();
	}
    
 	private function _cleanUp(){
		if($this->src!=null)					//If text ot image used then the src is empty
			ImageDestroy($this->src);
		ImageDestroy($this->dst);	
	}
/*-------------- Validating -------------------------*/    
    function isImage ($path=null){
    	
    	if ($path==null || $path=='')
			$this->fatal_error("Error: no image was specified",400,"Bad Request");

    	if (!file_exists($path))
			  $this->fatal_error("Error: image does not exist:$path",404,"Not Found");

		$this->size	= GetImageSize($path);
		
		$mime	= $this->size['mime'];    
		// $width			= $this->size[0];
		// $height			= $this->size[1];
		
		if (substr($mime, 0, 6) != 'image/')
			$this->fatal_error("Error: requested file is not an accepted type: $path",400,"Bad Request");
    }
    
 /*----------------------- Image Manipulation functions ---------------*/   
    function resize($w=0,$h=0,$g=false,$outPath=null){
    	$this->applyed='resize';
    	$this->maxWidth		= ($w==0 || $w=='')?0:$w;
		$this->maxHeight	= ($h==0 || $h=='')?0:$h;;
		$outputFunction=$this->outputFunction;
		$width	= $this->size[0];
		$height	= $this->size[1];
		
		$color=($this->bgcolor=='')?FALSE:$this->bgcolor;
		
		if (!$this->maxWidth && $this->maxHeight)
		{
			$this->maxWidth	= 99999999999999;
		}
		elseif ($this->maxWidth && !$this->maxHeight)
		{
			$this->maxHeight	= 99999999999999;
		}
		elseif (!$this->maxWidth && !$this->maxHeight)
		{
			$this->maxWidth		= $this->size[0];
			$this->maxHeight	= $this->size[1];
		}
		
		$offsetX	= 0;
		$offsetY	= 0;
		
		if ($this->cropratio!=null)		//Crop ratio provided only
		{
			$cropRatio		= explode(':', (string) $this->cropratio);
			if (count($cropRatio) == 2)
			{
				$ratioComputed		= $width / $height;
				$cropRatioComputed	= (float) $cropRatio[0] / (float) $cropRatio[1];
				
				if ($ratioComputed < $cropRatioComputed)
				{ // Image is too tall so we will crop the top and bottom
					$origHeight	= $height;
					$height		= $width / $cropRatioComputed;
					$offsetY	= ($origHeight - $height) / 2;
				}
				else if ($ratioComputed > $cropRatioComputed)
				{ // Image is too wide so we will crop off the left and right sides
					$origWidth	= $width;
					$width		= $height * $cropRatioComputed;
					$offsetX	= ($origWidth - $width) / 2;
				}
			}
		}
		elseif($this->cropOption!=null)
		{
			
			$cropRatio=$this->cropOption;
			/*$cropRatioComputed	= (float) $cropRatio['width'] / (float) $cropRatio['height'];
			*/
			$height		= $cropRatio['h']*($height/$cropRatio['height']);
			$width		= $cropRatio['w']*($width/$cropRatio['width']);
			$offsetX	= $cropRatio['x1']*$width/$cropRatio['width'];
			$offsetY	= $cropRatio['y1']*$height/ $cropRatio['height'];			
		}
		//echo $offsetX;
		//exit;

		$xRatio		= $this->maxWidth / $width;
		$yRatio		= $this->maxHeight / $height;
		
		if ($xRatio * $height < $this->maxHeight)
		{ // Resize the image based on width
			$this->tnHeight	= ceil($xRatio * $height);
			$this->tnWidth	= $this->maxWidth;
		}
		else // Resize the image based on height
		{
			$this->tnWidth	= ceil($yRatio * $width);
		 	$this->tnHeight	= $this->maxHeight;
		}

		
		$this->dst	= imagecreatetruecolor($this->tnWidth, $this->tnHeight);
		
		if (in_array($this->size['mime'], array('image/gif', 'image/png','image/x-png')))
		{
			if (!$color)
			{
				// If this is a GIF or a PNG, we need to set up transparency
				imagealphablending($this->dst, false);
				imagesavealpha($this->dst, true);
			}
			else
			{
				// Fill the background with the specified color for matting purposes
				if ($color[0] == '#')
					$color = substr($color, 1);
				
				$background	= FALSE;
				
				if (strlen($color) == 6)
					$background	= imagecolorallocate($this->dst, hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]));
				else if (strlen($color) == 3)
					$background	= imagecolorallocate($this->dst, hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2]));
				if ($background)
					imagefill($this->dst, 0, 0, $background);
			}
		}
		
		//echo $this->srcPath;
		//exit;
		//$cf=$this->creationFunction;
		//$this->dst=$cf($this->srcPath);
		
		
		
		
		// Resample the original image into the resized canvas we set up earlier
		ImageCopyResampled($this->dst, $this->src, 0, 0, $offsetX, $offsetY, $this->tnWidth, $this->tnHeight, $width, $height);
		
		
		if ($this->doSharpen)
		{
			// Sharpen the image based on two things:
			//	(1) the difference between the original size and the final size
			//	(2) the final size
			$sharpness	= $this->findSharp($width, $this->tnWidth);
			
			$sharpenMatrix	= array(
				array(-1, -2, -1),
				array(-2, $sharpness + 12, -2),
				array(-1, -2, -1)
			);
			$divisor		= $sharpness;
			$offset			= 0;
			imageconvolution($this->dst, $sharpenMatrix, $divisor, $offset);
		}
		
		if($g){
			$this->applyed='resize x gray';
			$this->ColorMap($this->dst);	
		}
		if($outPath==null)			//Request for onfly output
    		$this->OutputOnfly();
    	else 
    		$outputFunction($this->dst, $outPath, $this->quality);
    		
    }
        
    
    function gray($im=null,$outPath=null){
    	$outputFunction=$this->outputFunction;
    	$this->applyed='gray';
    	if($im==null){					//Just gray operation needed
    		$this->dst=$this->src;
    		$this->ColorMap($this->dst);
    	}
    	else{ 
    		 $this->dst=$im;
    		 $this->ColorMap($im);
    	}
    	
    	if($outPath==null)			//Request for onfly output
    		$this->OutputOnfly();
    	else 
    		$outputFunction($this->dst, $outPath, $this->quality);
    	//return $this->dst;
    }
	
    //$t= text
    //$font = font file name
    //$col= font color
    //$bg = $background color
    //$x=offsetX
    //$y=offsetY
    //$of=output file
    //$t= text
    //$font = font file name
    //$fs= fontsize
    //$col= font color
    //$bg = $background color
    //$x=offsetX
    //$y=offsetY
    //$of=output file
    function FromText($t=null,$font='',$fs='',$col='',$bg='',$x='',$y='',$of=null){
    	if($t==null || $t=='')
    		$this->fatal_error('Error : no text provided');
    	$this->text=$t;
    	
    	if($font!='')
    		$this->font_file=$font;
    	else 
    		$this->font_size=5;
    		    	
    	if($fs!='')
    		$this->font_size=($font=='' && $fs>5)?5:$fs;
    	if($col!='')
    		$this->font_color=$col;
    	if($bg!='')
    		$this->background_color=$bg;	
    	if($x!='')
    		$this->offsetX=$x;
    	if($y!='')
    		$this->offsetY=$y;
    	$this->draw($of);
    }
    
	private function draw($of=null){
        $outputFunction=$this->outputFunction;
		if($of=='')
			$of=null;
		$this->applyed='fromtext';
		$message=$this->text;
		$font_rgb = $this->hex_to_rgb($this->font_color) ;
		$background_rgb = $this->hex_to_rgb($this->background_color) ;		
		
		//$box = @ImageTTFBBox($font_size,0,$font_file,$text) ;
		
		if($this->font_file=='')				//No font defined
		{
			$width = ImageFontWidth(5) * strlen($message) ;
	        $height = ImageFontHeight(5);
	        $this->dst = ImageCreate($width,$height);
		}
		else{
			$dip = $this->get_dip($this->font_file,$this->font_size,$message) ;
			$box = @ImageTTFBBox($this->font_size,0,$this->font_file,$message) ;
			$this->dst = @ImageCreate(abs($box[2]-$box[0])+2,abs($box[5]-$dip)+2) ;
			$this->offsetX=-$box[0];
			$this->offsetY=abs($box[5]-$box[3])-$box[1];
		}
		
		//echo $message;
		//exit();
		$this->background_color = @ImageColorAllocate($this->dst,$background_rgb['red'],$background_rgb['green'],$background_rgb['blue']) ;
		$this->font_color = ImageColorAllocate($this->dst,$font_rgb['red'],$font_rgb['green'],$font_rgb['blue']) ;   
		
		if($this->font_file!='')
			ImageTTFText($this->dst,$this->font_size,$this->angel,$this->offsetX,$this->offsetY,$this->font_color,$this->font_file,$message) ;
		else 
			ImageString($this->dst,$this->font_size,$this->offsetX,$this->offsetY,$message,$this->font_color) ; 
			
		$this->quality=round(10 - ($this->quality / 10));
		
		if($this->transparent)
		    ImageColorTransparent($this->dst,$this->background_color) ;
			
		if($of=='')			//Request for onfly output
    		$this->OutputOnfly();
    	else 
    		$outputFunction($this->dst, $of, $this->quality);
	}
	
	function get_dip($font,$size,$test='')
	{
		$test_chars =($test!='')?$test:'abcdefghijklmnopqrstuvwxyz' .
				      'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
					  '1234567890' .
					  '!@#$%^&*()\'"\\/;.,`~<>[]{}-+_-=' ;
		$box = @ImageTTFBBox($size,0,$font,$test_chars) ;
		return $box[3] ;
	}
	
	function hex_to_rgb($hex)
	{
	
	    if(substr($hex,0,1) == '#')
	        $hex = substr($hex,1) ;
	
	    // expand short form ('fff') color
	    if(strlen($hex) == 3)
	    {
	        $hex = substr($hex,0,1) . substr($hex,0,1) .
	               substr($hex,1,1) . substr($hex,1,1) .
	               substr($hex,2,1) . substr($hex,2,1) ;
	    }
	    if(strlen($hex) != 6)
	        $this->fatal_error('Error: Invalid color "'.$hex.'"') ;
	
	    // convert
	    $rgb['red'] = hexdec(substr($hex,0,2)) ;
	    $rgb['green'] = hexdec(substr($hex,2,2)) ;
	    $rgb['blue'] = hexdec(substr($hex,4,2)) ;
	    return $rgb ;
	}
/*---------------- Filter  Functions -----------------------*/
	
	# where col is 0 for grey, 1 for red, 2 green, 3 blue , -1 for invert
    function ColorMap(&$im,$col=0,$dither=1) {    
	    if (!($t=imagecolorstotal($im))) {
	        $t = 256;
	        imagetruecolortopalette($im, $dither, $t);    
	    }
	    for($i=0; $i<$t; $i++){   
	        $old=ImageColorsForIndex($im,$i);
	     
		     $commongrey=(int)($old[red]+$old[green]+$old[blue])/3;
	       
	        if($col==0){
		       $r=$g=$b=$commongrey;
		     }elseif($col==-1){
		       $r=$t-$old[red]-1;
		       $g=$t-$old[green]-1;
		       $b=$t-$old[blue]-1;;
		     }elseif($col==1){
		       $r=$commongrey;
		       $g=$b=0;
		     }elseif($col==2){
		       $g=$commongrey;
		       $r=$b=0;
		     }elseif($col==3){
		       $b=$commongrey;
		       $g=$r=0;
		    }
		    ImageColorSet($im,$i,$r,$g,$b);
	    }
	}
	
  
 /*--------------- Error Output ----------------*/
  function fatal_error($message,$err=500,$hstr="Internal Server Error")
	{
	    // send an image
	    if(function_exists('ImageCreate') && $this->SmartMessage)
	    {
	        $width = ImageFontWidth(5) * strlen($message) + 10 ;
	        $height = ImageFontHeight(5) + 10 ;
	        if($image = ImageCreate($width,$height))
	        {
	            $background = ImageColorAllocate($image,255,255,255) ;
	            $text_color = ImageColorAllocate($image,255,0,0) ;
	            ImageString($image,5,5,5,$message,$text_color) ;    
	            header('Content-type: image/png') ;
	            ImagePNG($image) ;
	            ImageDestroy($image) ;
	            exit ;
	        }
	    }
	
	    header("HTTP/1.1 $err $hstr") ;
	    print($message) ;
	    exit ;
	}    
    
}
/* -------------------- END OF SmartImg CLASS --------------------*/

/* ------------------- Auxiliary Classes -------------------------*/

class T2I{
	
	private $text				= '';
	
	function T2I(){
		
	}
	
}
//FIX BMP IMAGE
function ImageCreateFromBmp($filename)
{
 //Ouverture du fichier en mode binaire
   if (! $f1 = fopen($filename,"rb")) return FALSE;

 //1 : Chargement des ent?tes FICHIER
   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
   if ($FILE['file_type'] != 19778) return FALSE;

 //2 : Chargement des ent?tes BMP
   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] = 4-(4*$BMP['decal']);
   if ($BMP['decal'] == 4) $BMP['decal'] = 0;

 //3 : Chargement des couleurs de la palette
   $PALETTE = array();
   if ($BMP['colors'] < 16777216)
   {
    $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
   }

 //4 : Cr?ation de l'image
   $IMG = fread($f1,$BMP['size_bitmap']);
   $VIDE = chr(0);

   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
   $P = 0;
   $Y = $BMP['height']-1;
   while ($Y >= 0)
   {
    $X=0;
    while ($X < $BMP['width'])
    {
     if ($BMP['bits_per_pixel'] == 24)
        $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
     elseif ($BMP['bits_per_pixel'] == 16)
     { 
        $COLOR = unpack("n",substr($IMG,$P,2));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 8)
     { 
        $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 4)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 1)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1] >>7;
        elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
        elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
        elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
        elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
        elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
        elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
        elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     else
        return FALSE;
     imagesetpixel($res,$X,$Y,$COLOR[1]);
     $X++;
     $P += $BMP['bytes_per_pixel'];
    }
    $Y--;
    $P+=$BMP['decal'];
   }

 //Fermeture du fichier
   fclose($f1);

 return $res;
}

// FIX imageconvolution function for older version php
if(!function_exists('imageconvolution')){
	function imageconvolution($src, $filter, $filter_div, $offset){
	    if ($src==NULL) {
	        return 0;
	    }
	    
	    $sx = imagesx($src);
	    $sy = imagesy($src);
	    $srcback = ImageCreateTrueColor ($sx, $sy);
	    ImageCopy($srcback, $src,0,0,0,0,$sx,$sy);
	    
	    if($srcback==NULL){
	        return 0;
	    }
	        
	    #FIX HERE
	    #$pxl array was the problem so simply set it with very low values
	    $pxl = array(1,1);
	    #this little fix worked for me as the undefined array threw out errors
	
	    for ($y=0; $y<$sy; ++$y){
	        for($x=0; $x<$sx; ++$x){
	            $new_r = $new_g = $new_b = 0;
	            $alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
	            $new_a = $alpha >> 24;
	            
	            for ($j=0; $j<3; ++$j) {
	                $yv = min(max($y - 1 + $j, 0), $sy - 1);
	                for ($i=0; $i<3; ++$i) {
	                        $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
	                    $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
	                    $new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
	                    $new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
	                    $new_b += ($rgb & 0xFF) * $filter[$j][$i];
	                }
	            }
	
	            $new_r = ($new_r/$filter_div)+$offset;
	            $new_g = ($new_g/$filter_div)+$offset;
	            $new_b = ($new_b/$filter_div)+$offset;
	
	            $new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
	            $new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
	            $new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);
	
	            $new_pxl = ImageColorAllocateAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
	            if ($new_pxl == -1) {
	                $new_pxl = ImageColorClosestAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
	            }
	            if (($y >= 0) && ($y < $sy)) {
	                imagesetpixel($src, $x, $y, $new_pxl);
	            }
	        }
	    }
	    imagedestroy($srcback);
	    return 1;
	}
}
