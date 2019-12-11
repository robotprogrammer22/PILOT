<?php


?>

 <div id="full" style="margin:10px;overflow:hidden;" class="shadow">
  <div id="downloadPanel" >

  <h1><span style="color: #000;">FAQ</span></h1><br/>

  <h2><span>What is PILOT?</span></h2>
<p>
    PILOT (Planetary Image Locator Tool) is an online data portal used by planetary scientists and cartographers to search through the raw archives of the <a href="http://astrogeology.usgs.gov/facilities/cartography-and-imaging-sciences-node-of-nasa-planetary-data-system" target="_blank"><b>PDS Cartography and Imaging Science Node</b></a>. It searches through a large catalog of visual, infrared and spectrometer camera images taken of planetary bodies in our solar system. For information on specific missions, please visit the <a href="http://pds-imaging.jpl.nasa.gov/portal/"><b>Data Portal</b></a>.  The development of PILOT is supported by the USGS Astrogeology Science Center and the NASA/USGS PDS Cartography and Imaging Science Node.
</p>
<br/><br/>
  <h2><span>What is the difference between mapped and unmapped image sets?</span></h2>
<ul><li> <span class="orangeText"><strong>Mapped</strong></span> images have targets that generally fill or nearly fill the instrument field of view, so they can be mapped onto a planetary surface and have lat/long coordinates.</li>
<li><span class="orangeText"><strong>Unmapped</strong></span> images have targets that do not generally fill the instrument field of view and thus they have not typically been mapped onto a planetary surface and may have limited associated spatial data. The images may represent approach or cruise views (showing a full planet, limb or terminator), or they may have been acquired for calibration. Some images also may have had errors during ISIS processing because of improper labels or missing spacecraft information. Lat/Lon and photometric keywords are unavailable. NOTE: Although the images cannot be mapped, they still may contain quality imagery.</li></ul>
<br/><br/>
  <h2><span>How accurate are the coordinates of PILOT images?</span></h2>
   <p>PILOT relies on the <span class="orangeText"><strong>Unified Planetary Coordinate (UPC)</strong></span> database maintained by the USGS Astrogeology Scient Center to map images. The UPC is a database containing improved geometric and positional information about planetary image data that has been computed using a uniform coordinate system and projected onto a common (preferably 3D) planetary surface shape. The goals of the UPC are to build a uniform geometric database for all planetary orbital remote sensing data using the most current coordinate system and to make this database available to the scientific community in a variety of useful forms. For the end user, tools such as PILOT that utilize the UPC provide the most accurate geometry, coordinates, and positional information for images taken by various spacecraft. The database is regularly being updated with both new and recalculated image data as improved location data is made available.</p>
<br/><br/>
<h2>How are PILOT images processed?</h2>
<p>Positional and instrumental ‘metadata’ are extracted from PDS image labels and used to calculate detailed geometric data for a given image in the UPC database. The database is populated with up-to-date SPICE kernels, and improved pointing and location data are calculated for corners, edges, and for potentially every pixel in an image. The UPC benefits from image positional refinements resulting from cartographic processing and map development at USGS. The USGS Integrated Software for Imagers and Spectrometers (ISIS) system is the primary tool for computing, maintaining, and continually improving the UPC database. An ISIS camera model for a given imaging instrument is required for ingestion of image data into the UPC.</p>
<br/><br/>
 <h2>For ongoing missions, how current is the data in PILOT?</h2>
 <p>We attempt to keep current with each PDS release of ongoing missions. It can take four weeks to process a set of imagery after a PDS release.  Also some missions will fall behind in processing if mission labels change, new positional data is released, or the processing steps change to increase the accuracy of locational data.
</p>
<br/><br/>
 <h2>How complete are the image sets in PILOT?</h2>
 <p>We strive to provide every recorded image taken for each instrument included in PILOT. Images that contain errors or fail during processing are included in the unmapped image sets. Images that fail to be associated with a planet, moon or asteroid will be listed among Untargeted data.
</p>
<br/><br/>
<h2>How do I download images?</h2>
<p><span style="float:left;"> To download individual images, click on the download arrow in the results sreen. If the browser fails to initiate the download, try right clicking on the arrow and saving.</span><a href="images/pilot3-download-button.png" target="_blank"><img style="float:left;margin-left:10px;height:100px;" title="click to enlarge" src="images/pilot3-download-button.png" /></a></p>
<br style="clear:both;"/><br/>
<p>
<span style="float:left;">To download (or process) a group of images you must first select the images you want to download by clicking the checkboxes.</span>
<a href="images/pilot3-select-box.png" target="_blank"><img style="float:left;margin-left:10px;height:100px;" title="click to enlarge" src="images/pilot3-select-box.png" /></a></p>
<br style="clear:both;"/><br/>
<p>
<span style="float:left;">After selecting a group of images, you must click on the download arrow in the "Select" box.</span>
<a href="images/pilot3-select-download.png" target="_blank"><img style="float:left;margin-left:10px;height:100px;" title="click to enlarge" src="images/pilot3-select-download.png" /></a></p>
<br style="clear:both;"/><br/>
</p>
<br/><br/>
 <h2>What are the options for downloading a selected group of images?</h2>
<p>PILOT offers the following download and processing options for a group of selected images:
<ul>
<li><span class="orangeText"><strong>CSV File:</strong></span> a comma-delimited file with the product id's and links to the footprints. This file can be opened up in most common spreadsheet programs.
</li>
<li><span class="orangeText"><strong>WGET script:</strong></span> a bash shell script that works in Unix-compatible shells. Once executed, the script will pull down the search results into the directory it is executed within.
</li>
<li>
<span class="orangeText"><strong>POW (Projection the Web):</strong></span> Map Projection (on the) Web Service is a free online service that transforms raw Planetary Data System (PDS) images to science-ready, map-projected images. POW uses PDS Imaging Node tools (PILOT and UPC) to locate images and then allows the user to select and submit individual images to be map-projected. This process uses Astrogeology’s image processing package called the Integrated Software for Imagers and Spectrometers (ISIS, currently in version 3). By using ISIS, POW provides users with calibrated cartographic images that can be used readily for geologic mapping,change detection, merging of dissimilar instrument images, analysis in a Geographic Image System (GIS) and use in a host of other scientific applications (e.g., ArcMAP, ENVI, Matlab, JMARS, QGIS, etc.). POW is dependent on ISIS and the instruments it supports. As new instruments are added to ISIS, POW will also increase the number of instruments it supports.
</li>
</ul>
</p>
<br/><br/>
 <h2>How do I work with downloaded images?</h2>
<p>
Nearly all planetary data taken by NASA or other non-US spacecraft are stored in a Planetary Data System (PDS) standard.  Most of these holdings are cataloged in their original raw instrument form with descriptive ASCII labels to describe the resulting image or data set. The image files typically have an IMG suffix. They may also have a detached label with a LBL suffix. Transforming PDS data into spatially located “science-ready” products can be a complex process and usually requires specialized image-processing software like <a href="isis.astrogeology.usgs.gov" target="_blank"><b>ISIS</b></a> (Integrated Software for Imagers and Spectrometers) or <a href="http://www-mipl.jpl.nasa.gov/vicar.html" target="_blank"><b>VICAR</b></a> (Video Image Communication and Retrieval). Along with geometrical correction, these systems can also correct camera distortions, update radiometry parameters, perform photometric corrections and combine images into mosaics via bundle adjustment techniques. Radiometric calibration recalculates the values in an image based on exposure time, flat-field observations, dark current observations and other factors describing the unique electronics design and characteristics of an imaging system. Photometric corrections help to adjust images acquired under different illumination and viewing geometries such that the resulting images appear as if obtained under uniform conditions by adjusting the brightness and contrast.
<br/><br/>
It should be noted that the use of ISIS, VICAR, or other software to transfer PDS image data to a map-projected space does not guarantee that the resulting map coordinates of features will be strictly accurate. Positional accuracy depends on the ability to measure or reconstruct the position of the spacecraft and the pointing of the instrument as each observation was taken, as well as knowledge of the shape and rotation of the target body. The predicted (as opposed to reconstructed) position and pointing data that are sometimes used to make uncontrolled mosaics can be wildly erroneous, especially for pre-1990s missions, leading to maps with visible mismatches at image boundaries and systematic position errors of many kilometers. Thereform, for planetary mapping, Both ISIS and VICAR are used with associated photogrammetric software that estimates improved position, pointing, and planetary parameters based on measurements of matching ground features where images overlap.
</p>
<br/><br/>
<h2>What is the Stereo tab?</h2>
<p> The Stereo tab allows users to search for "strereo pairs" among a result set. Planetary scientists and cartographers use stereo pairs to create topographic models, slope and roughness analysis; landing site determination; wind, water, landslide and lava flow modelling; orthorectification for cartographic products, anaglyph creation, simulated 3D flyovers, and more. For more information, see <a href="http://www.hou.usra.edu/meetings/lpsc2015/pdf/1074.pdf"><b>LPSC 46 abstract #1074</b></a>.
</p>
<br/><br/>
<h2>What are the values set on the Advanced tab?</h2>
<p> The Advanced tab allows users to narrow their search by constraining the photometric values. Photometric values determine where the planetary body, spacecraft, and sun were located when the image was taken. For more information visit the <a href="http://astrogeology.usgs.gov/site/glossary" target="_blank" ><b>astogeology glossary</b></a>.
</p>

<br/><br/><br/><br/>

 </div>
</div>

<script>
window.onload = function() {
  var heightOffset = 140;
  var newHeight = ($(window).height() -heightOffset);
  $('#full').height(newHeight);
};
</script>
