<h2>Popular Classes</h2>
<?php foreach ($popular_classes as $c): ?>
	<a href="<?php echo base_url('class/'.$c->id); ?>" class="class-badge" data-id="<?php echo $c->id; ?>">
		<div class="class-title">
			<?php echo $c->title; ?>
			<div class="class-neighborhood"><?php echo $c->neighborhood; ?>&nbsp;</div>
		</div>
		<div class="class-info">
			<div class="class-ages"><?php echo $c->age; ?></div>
			<?php 
					$score = ($c->score===null)? "--":number_format($c->score,1);   
					$bubble = ($c->score===null)? "null":floor($score);
			?>
			<div class="class-score s-<?php echo $bubble; ?>"><?php echo $score; ?></div>
			<div class="score-bottom">CityTot Score</div>
			<div class="score-bottom number-reviews"><?php echo $c->num_reviews; ?> reviews</div>
		</div>
	</a>
<?php endforeach; ?>