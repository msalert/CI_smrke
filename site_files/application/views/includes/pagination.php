<div class="pagination-box">
				<?php
					$num_pages = ceil($results->num_rows/10);
					$current_page = $results->page_num;
					$_GET['page'] = isset($_GET['page'])?$_GET['page']:1;
					$url_no_page = preg_replace('/&?page=[^&]*/', '', $url); 
				?>
				
				<?php if($results->page_num>1): ?>
					<?php  // previous page
						echo "<a class='class_sorting_link_pagination' href=\"".$url_no_page."&page=".($_GET['page']-1)."\"><</a>";
					?>
				<?php endif; ?>
				<?php if($current_page>2 && $num_pages>5) echo "<a class='class_sorting_link_pagination ' href=\"".$url_no_page."&page=1"."\">1</a>"; ?>
				<?php if($num_pages>1):  ?>
					<?php 
						// show page numbers
						if($num_pages<6){
							for($i=1;$i<=$num_pages;$i++){
								$selected = ($i==$current_page)? "selected":""; 
								echo "<a class='class_sorting_link_pagination ".$selected."' href=\"".$url_no_page."&page=".$i."\">".$i."</a>";
							}
						}else{
							if($current_page>(3)) echo "<span class='pagination_ellipses'>...</span>";
							if($num_pages > 20 && $current_page>10){
								$page_number = round($current_page/2);
								echo "<a class='class_sorting_link_pagination ' href=\"".$url_no_page."&page=".$page_number."\">".$page_number."</a><span class='pagination_ellipses'>...</span>";	
							}
							
							for($i=max(array($current_page-1,1));$i<=min(array($current_page+1,$num_pages));$i++){
								$selected = ($i==$current_page)? "selected":""; 
								echo "<a class='class_sorting_link_pagination ".$selected."' href=\"".$url_no_page."&page=".$i."\">".$i."</a>";
							}
							
							if($num_pages > 20 && $current_page<($num_pages - 10)){
								$page_number = round(($num_pages + $current_page)/2);
								echo "<span class=' pagination_ellipses'>...</span><a class='class_sorting_link_pagination ' href=\"".$url_no_page."&page=".$page_number."\">".$page_number."</a>";	
							}
							if($current_page<($num_pages-2)) echo "<span class='pagination_ellipses'>...</span>";
							 
						}
						 
					?>
				<?php endif; ?>
				<?php if($current_page<($num_pages-1)&&$num_pages>5) echo "<a class='class_sorting_link_pagination' href=\"".$url_no_page."&page=".$num_pages."\">$num_pages</a>"; ?>
				<?php if($results->num_rows > ($results->page_num * 10)): ?>
					<?php  // next page
						echo "<a class='class_sorting_link_pagination' href=\"".$url_no_page."&page=".($_GET['page']+1)."\">></a>";
					?>
				<?php endif; ?>
				
			</div>