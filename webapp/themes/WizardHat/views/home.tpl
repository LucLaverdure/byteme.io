[header.tpl]
<form action="/people" method="post">
<div class="main-bg cf bg">
	<div class="sub-wrapper">
		<div class="box">
			<div class="request-info" style="display:none;">
				<h1> We need to gather information about you and your interests in order to provide you with content that matters to you.</h1>
				<h2> Your Interests:</h2>
				<p>
					<ul id="tags">
						<li>Ninjas</li>
						<li>Pirates</li>
						<li>Batman</li>
					</ul>
				</p>
				
				<script type="text/javascript">
					$(document).ready(function() {
						$("#tags").tagit();
					});
				</script>
				
				<!-- Facebook Login -->
				<div class="right fblogin">
					<div class="fb-login-button" data-width="200" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="true" data-use-continue-as="true"
					data-scope="public_profile,user_location,email,user_birthday,user_location,user_likes,user_work_history"
					onlogin='document.location.href=document.location.href;'>></div>
				</div>
				
				<!--
				<h2>My info:</h2>
				<div id="my-info">
					I speak <span class="lang"></span>,<br/>
					I am located @<span class="country"></span>, <span class="state"></span><br/>
					My IP is <span class="ip">[whatismy.ip]</span> 
				</div>
				
				<script type="text/javascript">
					$(document).ready(function() {			
						$.ajax({
							url: "https://geoip-db.com/json/",
							dataType: 'json',
							context: document.body,
							success: function(data, textStatus, jqXHR) {
								$("#my-info .country").text(data.country_name);
								$("#my-info .state").text(data.state);
							}
						});
					});

				</script>
				<script>
					$.browserLanguage(function( language , acceptHeader ){
						$("#my-info .lang").text(language);
					});
				</script>
				-->
			</div>
			<div class="logged_in people" style="display:none;background:#333;">
				
				<div class="col-lg-4 col-sm-12">
					<img src="" class="photo" width="240" height="240" alt="Picture" />

					<!-- Facebook Login -->

					<div class="fblogin">
						<div class="fb-login-button" data-width="200" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="true" data-use-continue-as="true"
						data-scope="public_profile,user_location,email,user_birthday,user_location,user_likes,user_work_history" onlogin='document.location.href=document.location.href;'></div>
					</div>
				</div>
				<div class="col-lg-8 col-sm-12">
					<h1></h1>
					<h2></h2>	
					<h3></h3>
					<p class="email-to">Email: <a class="email" href="mailto:"></a></p>
					<a href="" class="watch"><img src="/theme/files/img/rad.png" width="50" /> <span>Access myCBC</span></a>
				</div>
				
				<div class="likes" style="clear:left;">
				</div>
				
				<a href="" class="watch right"><img src="/theme/files/img/rad.png" width="50" /> <span>Access myCBC</span></a>
			</div>
<script type="text/javascript">
	$(document).on("click", "a.watch", function() {
		
	});
</script>			
<script type="text/javascript">

function statusChangeCallback(resp) {
	if (resp.status == "connected") {
		FB.api(
'/me?fields=id,name,picture.type(large),age_range,birthday,email,music{name},movies{name},books{name},television{name},work,favorite_athletes,games{name},location',
		  'GET',
		  {},
		  function(response) {
				console.log(response);

				// hide login page
				$(".request-info").hide();
				
				// display info page
				$(".logged_in").show();
				$(".bg").removeClass("main-bg").addClass("people-bg");
				
				// set info
				$(".logged_in .photo").attr("src", response.picture.data.url);
				$(".logged_in h1").text(response.name);
				$(".logged_in h2").text($.trim(response.work[0].position.name+" @ "+response.work[0].employer.name))	;
				$(".logged_in h3").text(response.location.name);
				$(".logged_in .email").attr('href',"mailto:"+response.email);
				$(".logged_in .email").text(response.email);
				
				$(".logged_in .likes").append('<h2>Athletes</h2>');
				for(var i=0;i<response.favorite_athletes.length;++i) {
					$(".logged_in .likes").append('<a class="like">'+response.favorite_athletes[i].name+"<span></span></a>, ");
				}

				$(".logged_in .likes").append('<h2>Books</h2>');
				for(var i=0;i<response.books.data.length;++i) {
					$(".logged_in .likes").append('<a class="like">'+response.books.data[i].name+"<span></span></a>, ");
				}
				
				$(".logged_in .likes").append('<h2>Television</h2>');
				for(var i=0;i<response.television.data.length;++i) {
					$(".logged_in .likes").append('<a class="like">'+response.television.data[i].name+"<span></span></a>, ");
				}
				for(var i=0;i<response.movies.data.length;++i) {
					$(".logged_in .likes").append('<a class="like">'+response.movies.data[i].name+"<span></span></a>, ");
				}
				
				$(".logged_in .likes").append('<h2>Music</h2>');
				for(var i=0;i<response.music.data.length;++i) {
					$(".logged_in .likes").append('<a class="like">'+response.music.data[i].name+"<span></span></a>, ");
				}

				$(".logged_in .likes").append('<h2>Games</h2>');
				for(var i=0;i<response.games.data.length;++i) {
					$(".logged_in .likes").append('<a class="like">'+response.games.data[i].name+"<span></span></a>, ");
				}

		  }
		);
	} else {
		$(".request-info").show();
		$(".logged_in").hide();
		$(".bg").addClass("main-bg").removeClass("people-bg");
		
	}
}
	  (function(d, s, id){
		 var js, fjs = d.getElementsByTagName(s)[0];
		 if (d.getElementById(id)) {return;}
		 js = d.createElement(s); js.id = id;
		 js.src = "https://connect.facebook.net/en_US/sdk.js";
		 fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));

  window.fbAsyncInit = function() {
    FB.init({
      appId      : '219606165444298',
      cookie     : true,
      xfbml      : true,
      version    : 'v2.12',
	  display: "popup",
	  redirect_uri: "https//byteme.io/close",
	  status     : true 
    });
      
    FB.AppEvents.logPageView(); 
	
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
	});
	
  }
  
	
</script>

		</div>
	</div>
</div>
</form>
[footer.tpl]