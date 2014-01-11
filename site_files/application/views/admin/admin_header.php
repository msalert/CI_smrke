<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="<?php echo base_url(); ?>">site</a>
            <div class="nav-collapse collapse">
                <ul class="nav left-nav">
                    <li class="dropdown">
                    	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Administration<b class="caret"></b></a>
                    	<ul class="dropdown-menu">
<!--                            <li><a href="<?php echo base_url('site-administration'); ?>">Site</a></li>
                            <li><a href="<?php echo base_url('site-administration/users'); ?>">Users</a></li>
                            <li><a href="<?php echo base_url('site-administration/reviews'); ?>">Reviews</a></li>-->
                            
                        </ul>
                    </li>
                    
                    
                </ul>
                <ul class="nav right-nav pull-right">
                    <li class="dropdown ">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">user<b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo base_url('logout'); ?>">Logout</a></li>
                            <!-- <li class="divider"></li>
                            <li class="nav-header">My Settings</li>
                            <li><a href="#">Change Password</a></li> -->
                            
                        </ul>
                    </li>
                    <?php if($system_message): ?>
                    	<li class="system-message"><?php echo $system_message; ?></li>
                    <?php endif; ?>
                </ul>
                
            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>