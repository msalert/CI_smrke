<div class="block-wr container">
	<h2 class='page-title-b'>Whoops!</h2><br /><br />
	<div class="oops">Something went wrong somewhere...</div>

<?php if(isset($ref)): ?>
	<div class="reference-num">
		If you'd like to get in touch with us concerning this error - 
		please email us at <a href="mailto:info@citytot.com">info@citytot.com</a> 
		and paste in the following reference number:<br>
		<div class="centered"><?php echo $ref; ?></div> 
	</div>
<?php endif; ?>
	<br />
	<div class="error-image centered"><img src="<?php echo base_url('_/images/error-img.jpg'); ?>" alt="" /></div>
	<br /><br />
	<div class="small-logo-image centered"><img src="<?php echo base_url('_/images/solo-logo-white.png'); ?>" alt="" /></div>
</div>
<div class="container scallop-bottom"></div>