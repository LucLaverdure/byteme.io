[header.tpl]
<form action="/people" method="post">
<div class="main-bg cf">
	<div class="sub-wrapper">
		<div class="box">
			<span class="ppl"><label><input type="radio" name="role" value="ppl" checked="checked" /> Person</label></span>
			<span class="job"><label><input type="radio" name="role" value="job" /> Job</label></span>
			<p><input type="text" name="query" class="query" /></p>
			<h2>Talents Required:</h2>
			<p>
				<ul id="tags">
					<li>Ninja</li>
				</ul>
			</p>
			
			<script type="text/javascript">
				$(document).ready(function() {
					$("#tags").tagit();
				});
			</script>

			<h2>Scoping Trail:</h2>
			<div id="s-trail" style="position:relative;">
				<span class="word" style="top:10px;left:10px;">Is</span>
				<span class="word" style="top:10px;left:100px;">Bird</span>
				<span class="word" style="top:10px;left:200px;">The</span>
				<span class="word" style="top:10px;left:300px;">Word</span>
				<span class="word" style="top:10px;left:400px;">?</span>
			</div>
			<script type="text/javascript">
				var wordset = [];
				function refreshWords() {
					$("#s-trail .word").each(function() {
						$this = $(this);
						$this.animate({
							"left": (0-$this.width())
						}, 8000, function() {
							$this.remove();
							$("#s-trail").append('<span class="word" style="top:10px;left:1024px;">'+wordset [Math.floor(Math.random() * wordset.length)]+'</span>');
							refreshWords();
						});
					});
				}
				
				$(document).on('click', ".word", function(evt) {
					$this = $(this);
					$("#tags").tagit("createTag", $this.text());
				});
				/*
				$(document).on('keyup', ".query", function(evt) {
					$this = $(this);
					if ($this.length >= 3) {
						$("#s-trail").text(function () {
							return ($this.text().replace($this.val(),
									'<span class="highlight">'+$this.val()+'</span>'));
						});​​​​​
						addsearch();
					}
				});			
				*/
				$(document).ready(function() {
					$.ajax({
						url: "/theme/files/static/words.en.txt",
						dataType: 'text',
						context: document.body,
						success: function(data, textStatus, jqXHR) {
							data = data.split("\n");
							for(var i = 0;i < data.length;++i){
								if ($.trim(data[i]) != '')
									wordset.push($.trim(data[i]));
							}
							refreshWords();
						}
					});
					
				});
				function addsearch() {
					$("#s-trail .word").remove();
					var counter = 0;
					for (var i=0;i<wordset.length;++i) {
						$this = $(this);
						var searchedWord = ''+wordset [i].word;
						if (searchedWord.indexOf($("input.query").val()) != -1) {
							$("#s-trail").append('<span class="word" style="top:10px;left:1024px;">'+searchedWord+'</span>');
							counter++;
						}
						if (counter >= 5) return;
					}
				}
			</script>

			<h2>Related Trail:</h2>
			<div id="r-trail"></div>

			<input id="search-button" class="right" type="submit" value="Search" />

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

		</div>
	</div>
</div>
</form>
[footer.tpl]