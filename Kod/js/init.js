var name = "";
var diet = "";
var id = "";

$(document).ready(function() {
	$("#profile-button").hide();
  	$("#random-button").hide();
  	
	window.fbAsyncInit = function() {
		FB.init({
			appId      : '',
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true  // parse XFBML
	  	});
		FB.Event.subscribe('auth.authResponseChange', function(response) {
			if (response.status === 'connected') {
				runApplication();
			} else {
				getFrontPage();
		    }
		});
	 };
	// Load the SDK asynchronously
	(function(d){
 		var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
 		if (d.getElementById(id)) {return;}
 		js = d.createElement('script'); js.id = id; js.async = true;
 		js.src = "//connect.facebook.net/sv_SE/all.js";
 		ref.parentNode.insertBefore(js, ref);
	}(document));
	
	query = getQueryString();
	if (query == "profile") {
		getProfilePage();
	} else if (query != "") {
		showRecipe(query);
	} else {
		getFrontPage();
	}
	
	window.onpopstate = function(e) {
		query = getQueryString();
		if (query == "profile") {
			getProfilePage();
		} else if (query != "") {
			showRecipe(query);
		} else {
			getFrontPage();
		}
	}
	
	// Set click-events
	$("#random-button").click(function(e) {
		generateRecipe();
		e.preventDefault();
	});
	$("#profile-button").click(function(e) {
		setNewPage("profile");
		getProfilePage();
		e.preventDefault();
	});
	$("#brand").click(function(e) {
		setNewPage("");
		getFrontPage();
		e.preventDefault();
	});
});

function runApplication() {
	FB.api('/me', function(response) {
		try {
			// Test if user is saved in db, then get diet - otherwise save user
			var accessToken = $("#accessToken").val();
			$.ajax({
				type: "POST",
				url: "user.php",
				data: { funct: "addUser", name: response.name, id: response.id, accessToken: accessToken }
			}).done(function(data) {
				var str = data.split(";");
				if (str[0] == "User found" || data == "User saved") {
					
					$("#profile-button").show();
					$("#random-button").show();
					if (str[0] == "User found" && str[1] != "") {	
						diet = str[1];
						name = response.name;
						id = response.id;
					} else {				// New user, need to init diet to db
						name = response.name;
						id = response.id;
						chooseDiet();
					}
				} else {
					throw "data";
				}
			});
		} catch (exception) {
			$("#content .alert-dismissable").remove();
			$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Ett oväntat fel inträffade.</div>");
		}
	});
}
function generateRecipe() {
	$.ajax({
		type: "GET",
		url: "recipe.php",
		data: { funct: "getRandomUserRecipeID", id: id, diet: diet }
	}).done(function(data) {
		if (data != "Error") {
			setNewPage("recipeID="+data);
			showRecipe(data);
		} else {
			$("#content").empty();
			$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Ett fel inträffade och det gick inte att hämta receptet.</div>");
		}
	});
}
function chooseDiet() {
	$("#content").empty();
	var html = "<div id='veg-div' class='padding'><h3>";
	if (diet == "") {
		html += "Välkommen, "+name+"!</h3><p>För att få så passande recept slumpade som möjligt bör du välja din kost:</p><form id='veg-form'><div class='radio'><input type='radio' name='veg' value='all' checked id='all'><label for='all'>Allätare</label></div><div class='radio'><input type='radio' name='veg' value='veg' id='veg'><label for='veg'>Vegetarian</label></div><div class='radio'><input type='radio' name='veg' value='vegan' id='vegan'><label for='vegan'>Vegan</label></div><input type='submit' class='btn btn-default' id='veg-btn' value='Fortsätt' /></form></div>";
	} else {
		html += "Välj kost</h3><p>För att få så passande recept slumpade som möjligt bör du välja din kost:</p><form id='veg-form'>";
		if (diet == "all") {
			html += "<div class='radio'><input type='radio' name='veg' value='all' checked id='all'><label for='all'>Allätare</label></div><div class='radio'><input type='radio' name='veg' value='veg' id='veg'><label for='veg'>Vegetarian</label></div><div class='radio'><input type='radio' name='veg' value='vegan' id='vegan'><label for='vegan'>Vegan</label></div>";
		} else if (diet == "veg") {
			html += "<div class='radio'><input type='radio' name='veg' value='all' id='all'><label for='all'>Allätare</label></div><div class='radio'><input type='radio' name='veg' value='veg' checked id='veg'><label for='veg'>Vegetarian</label></div><div class='radio'><input type='radio' name='veg' value='vegan' id='vegan'><label for='vegan'>Vegan</label></div>";
		} else if (diet == "vegan") {
			html += "<div class='radio'><input type='radio' name='veg' value='all' id='all'><label for='all'>Allätare</label></div><div class='radio'><input type='radio' name='veg' value='veg' id='veg'><label for='veg'>Vegetarian</label></div><div class='radio'><input type='radio' name='veg' checked value='vegan' id='vegan'><label for='vegan'>Vegan</label></div>";
		}
		html += "<input type='submit' class='btn btn-default' id='veg-btn' value='Välj' /><input type='button' class='btn btn-default' id='veg-cancel' value='Avbryt' /></form><div>";
	}
	$("#content").append(html);
	if (diet != "") {
		$("#veg-cancel").click(function() {
			getProfilePage();
		});
	}
	$("#veg-form").submit(function(e) {
		diet = $("[name='veg']:checked").val(); // Get the selected value
		var accessToken = $("#accessToken").val();
		$.ajax({
			type: "POST",
			url: "user.php",
			data: { funct: "saveDiet", name: name, id: id, diet: diet, accessToken: accessToken }
		}).done(function(data) {
			if (data !== "Diet saved") {
				$("#content .alert-dismissable").remove();
				$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Ett fel inträffade när kostinformationen skulle sparas i databasen.</div>");
			} else {
				getProfilePage();
			}
		});
		e.preventDefault();
	});
}
function getFrontPage() {
	$("#content").empty();
	$("#content").append("<div class='padding'><img src='http://säsongsmat.nu//w/images/thumb/a/a9/346.JPG/300px-346.JPG' id='fimage' /><h3>Välkommen till FoodGen!</h3><p>Vid inloggning med facebook kan du slumpa fram recept, rata, favorisera (om du vill spara undan receptet utan att rata det) eller dela dem med dina vänner. Du kan även lista och hantera dina favoriserade och ratade recept på din profil.</p><p>Sidan hämtar recept från <a href='http://säsongsmat.nu/' target='_blank'>säsongsmat.nu</a>, så det är dit du bör vända dig om du vill lägga till recept som du saknar här! Recepten som läses in är ur kategorierna: Varmrätter, Förrätter och smårätter, Soppor och Sallader.</p></div>");
}
function getProfilePage() {
	$("#content").empty();
	$("#content").append("<div id='profile-div' class='padding'><h3>Profil</h3><p>Här kan du ändra dina inställningar och visa listor på recept du favoriserat eller ratat</p><br/><p><a href='#' id='change-diet'>Ändra kostinställning</a></p><p><a id='manage-favoured' href='#'>Visa favoritrecept</a></p><p><a id='manage-removed' href='#'>Visa ratade recept</a></p><br><p><a id='logout-button' href='#'>Logga ut</a></p></div>");
	$("#change-diet").click(function(e) {
		chooseDiet();
		e.preventDefault();
	});
	$("#logout-button").click(function(e) {
		FB.logout(function() {
        	window.location = "?"; // Have to reload the site to get logged out state
   		});
		e.preventDefault();
	});
	var backLink = "<input type='button' class='btn btn-default' id='back' value='Tillbaka till profil' />";
	$("#manage-favoured").click(function(e) {
		var accessToken = $("#accessToken").val();
		$.ajax({
			type: "POST",
			url: "recipe.php",
			data: { funct: "recipeGetComments", id: id, comment: "like", accessToken: accessToken }
		}).done(function(data) {
			$("#content").empty();
			var html = "";
			if (data != "NoneFound") {
				var recipes = $.parseJSON(data);
				html = "<ul class='list-unstyled'>";
				$.each(recipes, function(index, recipe) {
					html += "<li><a class='disfavour-link' href='?recipeID="+recipe.recipeID+"'>X</a> <a href='?recipeID="+recipe.recipeID+"'>"+recipe.title+"</a></li>";
				});
				html += "</ul>";
			} else {
				html = "<p>Det finns inga favoriserade recept.</p>";
			}
			$("#content").append("<div class='padding'><h3>Hantera favoritrecept</h3><p>Klicka på krysset vid receptet för att ta bort favorisering av receptet.</p>"+html+"<br>"+backLink+"</div>");
			
			$(".disfavour-link").click(function(e) {
				var href = $(this).attr("href");
				var recipeID = href.replace(/^\D+/g, "");
				if (recipeID != "") {
					$.ajax({
						type: "POST",
						url: "recipe.php",
						data: { funct: "recipeUserRemoveComment", id: id, recipeID: recipeID, accessToken: accessToken }
					}).done(function(data) {
						if (data == "Deleted") {
							$("#content .alert-dismissable").remove();
							getProfilePage();
							$("#content").prepend("<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Kommentaren raderades.</div>");
						} else {
							$("#content .alert-dismissable").remove();
							$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Det gick inte att ta bort kommentaren.</div>");
						}
					});
				}
				e.preventDefault();
			});
			$("#back").click(function(e) {
				$("#content").empty();
				getProfilePage();
				e.preventDefault();			
			});
		});
		e.preventDefault();
	});
	$("#manage-removed").click(function(e) {
		var accessToken = $("#accessToken").val();
		$.ajax({
			type: "POST",
			url: "recipe.php",
			data: { funct: "recipeGetComments", id: id, comment: "dislike", accessToken: accessToken }
		}).done(function(data) {
			$("#content").empty();
			var html = "";
			if (data != "NoneFound") {
				var recipes = $.parseJSON(data); 
				html = "<ul class='list-unstyled'>";
				$.each(recipes, function(index, recipe) {
					html += "<li><a class='disban-link' href='?recipeID="+recipe.recipeID+"'>X</a> <a href='?recipeID="+recipe.recipeID+"'>"+recipe.title+"</a></li>";
				});
				html += "</ul>";
			} else {
				html = "<p>Det finns inga ratade recept.</p>";
			}
			$("#content").append("<div class='padding'><h3>Hantera ratade recept</h3><p>Klicka på krysset vid receptet för att häva ratandet av receptet.</p>"+html+"<br>"+backLink+"</div>");
			$(".disban-link").click(function(e) {
				var href = $(this).attr("href");
				var recipeID = href.replace(/^\D+/g, "");
				if (recipeID != "") {
					$.ajax({
						type: "POST",
						url: "recipe.php",
						data: { funct: "recipeUserRemoveComment", id: id, recipeID: recipeID, accessToken: accessToken }
					}).done(function(data) {
						if (data == "Deleted") {
							$("#content .alert-dismissable").remove();
							getProfilePage();
							$("#content").prepend("<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Kommentaren raderades.</div>");
						} else {
							$("#content .alert-dismissable").remove();
							$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Det gick inte att ta bort kommentaren om receptet.</div>");
						}
					});
				}
				e.preventDefault();
			});
			$("#back").click(function(e) {
				$("#content").empty();
				getProfilePage();
				e.preventDefault();			
			});
		});
		e.preventDefault();
	});
}
function showRecipe(recipeID) {
	$.ajax({
		type: "GET",
		url: "recipe.php",
		data: { funct: "getRecipeByID", recipeID: recipeID }
	}).done(function(data) {
		if (data != "Error") {
			$("#content").empty();
			var recipe = $.parseJSON(data); 
			var favourButton = "<input type='button' class='btn btn-default' value='Favorisera recept' id='recipe-favour' />";
			var removeButton = "<input type='button' class='btn btn-default' id='recipe-remove' value='Rata recept' />";
			if (recipe.comment == "like") {
				favourButton = "<input type='button' class='btn btn-default' disabled='true' value='Favorisera recept' id='recipe-favour' />";
			} else if (recipe.comment == "dislike") {
				removeButton = "<input type='button' class='btn btn-default' disabled='true' id='recipe-remove' value='Rata recept' />";
			}
			var shareButton = "<a id='fb-link' target='_blank' href='https://www.facebook.com/sharer/sharer.php?u="+window.location+"'><input type='button' id='share-button' class='btn btn-default' value='Dela på facebook'></input></a>";
			$("#content").append("<div id='recipe-div' class='padding'>"+favourButton+removeButton+shareButton+"</div>");
			var html = "";
			document.title = "FoodGen - "+ recipe.title;
			html += "<input type='hidden' id='recipeID' value='"+recipe.recipeID+"' /><h3 id='title'>"+recipe.title+"</h3><p class='portions'>"+recipe.portions+"</p><br/>";
			if (recipe.pic != "-") {
				html += "<img id='image' src='"+recipe.pic+"' />";
			}
			$.each(recipe.ingredients, function(index, ingredient) {
				html += "<p>"+ingredient+"</p>";
			});
			html += "<br/><div class='instruction'>"+recipe.instruction+"</div>";
			html = html.replace("[", "");
			html = html.replace("]", "");
			
			$("#recipe-div").append(html);
			
			$("#recipe-remove").click(function() {
				// Happens when recipe is removed from generator
				var recipeID = $("#recipeID").val();
				var accessToken = $("#accessToken").val();
				if (id != "") {
					$.ajax({
						type: "POST",
						url: "recipe.php",
						data: { funct: "recipeUserComment", id: id, recipeID: recipeID, comment: "dislike", accessToken: accessToken }
					}).done(function(data) {
						console.log(data);
						if (data != "Error") {
							$("#content").prepend("<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Receptet '"+data+"' har ratats, och kommer inte visas igen. Du kan hantera ratade recept på din profil.</div>");
						} else {
							$("#content .alert-dismissable").remove();
							$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Det gick inte att rata receptet.</div>");
						}
					});
				} else {
					$("#content .alert-dismissable").remove();
					$("#content").prepend("<div class='alert alert-info alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Du måste logga in för att kunna rata receptet.</div>");
				}
			});
			$("#recipe-favour").click(function() {
				// Happens when recipe is favoured from generator
				var recipeID = $("#recipeID").val();
				var accessToken = $("#accessToken").val();
				if (id != "") {
					$.ajax({
						type: "POST",
						url: "recipe.php",
						data: { funct: "recipeUserComment", id: id, recipeID: recipeID, comment: "like", accessToken: accessToken }
					}).done(function(data) {
						if (data != "Error") {
							$("#content").prepend("<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Receptet '"+data+"' har favoriserats. \n\nDu kan hantera favoriserade recept på din profil.</div>");
						} else {
							$("#content .alert-dismissable").remove();
							$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Det gick inte att favorisera receptet.</div>");
						}
					});
				} else {
					$("#content .alert-dismissable").remove();
					$("#content").prepend("<div class='alert alert-info alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Du måste logga in för att kunna favorisera receptet.</div>");
				}
				
			});
		} else {
			$("#content").empty();
			$("#content").prepend("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Det gick inte att hämta receptet.</div>");
		}
	});
}

function setNewPage(query) {
	var url = "?"+query;
	history.pushState("", "FoodGen", url);
}
function getQueryString() {
	var query = window.location.search;
	query = query.match(/\?(.)*/);
	if (query != null) {
		query = query[0].replace("?", "");
		if (query == "profile") {
			return query;
		}
		if (/recipeID/.test(query)) {
			return query.replace("recipeID=", "");
		}
	}
	return "";
}
