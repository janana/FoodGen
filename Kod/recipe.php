<?php

require_once("db/config.php");
require_once("db/DAL.php");
require_once("db/RecipeDAL.php");

// Handle recipes in db
session_start();
$recipeDAL = new RecipeDAL();
$savedRecipes = $recipeDAL->getRecipes(); // Without category and ingredients

$format = "d-m-y";
$todaysDate = date($format);
$isUpdateTime = false;
$savedDate = file_get_contents("updateRecipe.txt");
if ($savedDate != $todaysDate) {
	// Time to update recipes
	$isUpdateTime = true;
	file_put_contents("updateRecipe.txt", $todaysDate);
}
/**
 * CategoryID:
 * 5. Vegetariska recept
 * 6. Veganska recept
 */
 
 /**
  * Comments:
  * dislike
  * like
  */
 
if ($isUpdateTime) {
	// Get the recipes from säsongsmat website
	$domain = "http://xn--ssongsmat-v2a.nu/";
	
	$url = $domain . "ssm/Kategori:Recept?action=render";
	$page = getPageFromURL($url);
	
	$links = getQuery($page, "//table[@class='responsivetable sortable']//tr//td[@data-title='Recept']/a");
	$categLinks = getQuery($page, "//table[@class='responsivetable sortable']//tr//td[@data-title='Kategori']");
	
	if ($links->length == $categLinks->length) {
		for ($i = 0; $i < $links->length; $i++) {
			$categLink = $categLinks->item($i)->childNodes->item(1);
			
			if ($categLink->nodeValue == "Varmrätter" ||
				$categLink->nodeValue == "Sallader" ||
				$categLink->nodeValue == "Soppor" ||
				$categLink->nodeValue == "Förrätter och smårätter") {
					
				$title = $links->item($i)->nodeValue;
				// Test if new recipes exist, if it's new, extract info and add to db
				$exists = false;
				foreach($savedRecipes as $recipe) {
					if ($recipe["title"] == $title) {
						$exists = true;
					}
				}
				if (!$exists) {
					$href = $links->item($i)->getAttribute("href");
					
					// Get each site
					if (preg_match('#(.*)Recept:(.*)#', $href, $out)) {
						$recipeURL = $domain."ssm/Recept:".$out[2] . "?action=render";
						$recipePage = getPageFromURL($recipeURL);
						
						// Pic
						$picNode = getQuery($recipePage, "//a[@class='image']/img");
						$pic = "-";
						if ($picNode->length > 0) {
							$pic = $domain . $picNode->item(0)->getAttribute("src");
						}
						
						// Portions
						$portionsNode = getQuery($recipePage, "//span[@itemprop='recipeYield']");
						$portions = " ";
						if ($portionsNode->length > 0) {
							$portions = $portionsNode->item(0)->nodeValue;
							$portions = str_replace("â€“", "", $portions);
						}
					
						// Instructions
						$instructionNodes = getQuery($recipePage, "//p[(not(@itemprop = 'recipeCategory') and not(./*[1][local-name() = 'br']) and not(./*[1][local-name() = 'i']) )] | //ol"); 
						$instruction = "";
						for ($k = 0; $k < $instructionNodes->length; $k++) {
							$instruction .= $instructionNodes->item($k)->nodeValue ."<br/>";
						}
						if ($instruction == "") {
							$instruction = "-";
						}
						
						
						// Categories
						$categoryNodesParent = getQuery($recipePage, "//p[@itemprop='recipeCategory']");
						$categoryNodes = $categoryNodesParent->item(0)->childNodes;
						$categories = array();
						foreach($categoryNodes as $categoryNode) {
							if ($categoryNode->hasAttributes()) {
								$categoryName = $categoryNode->getAttribute("title");
								switch ($categoryName) {
									case "Vegetariska recept":
										$categories[] = 5;
										break;
									case "Veganskt":
										$categories[] = 6;
										break;
									default:
										break;
								}
							}
						}
						
						
						// Ingredients
						$ingredients = array();
						$ingredientNodes = getQuery($recipePage, "//body/ul/li/span");
						foreach($ingredientNodes as $ingredientNode) {
							if ($ingredientNode->hasChildNodes()) {
								// Remove duplicated values
								$children = $ingredientNode->childNodes;
								foreach ($children as $child) {		
									if ($child->hasChildNodes()) {
										$spans = $child->childNodes;
										foreach ($spans as $span) {
											if ($span->hasAttributes()) {
												$span->removeAttribute("data-ssmchecks");
												if ($span->getAttribute("class") == "smwttcontent") {
													$child->removeChild($span);
												}
											}
										}
									}
								}
							}
							$ingredients[] = str_replace("Â", " ", $ingredientNode->nodeValue);
						}
						
						// Add recipe to db
						$recipeID = $recipeDAL->addRecipe($title, $pic, $portions, $instruction);
						// Add categories from recipe to db
						foreach($categories as $category) {
							$recipeDAL->addCategory($recipeID, $category);
						}
						foreach($ingredients as $ingredient) {
							$recipeDAL->addIngredient($recipeID, $ingredient);
						}
					}
				}
			}
		}
	} else {
		echo "Fel inträffade när recepten skulle läsas in från apiet";
	} 
}

if ($_GET["funct"] == "getRandomUserRecipeID") {
	$id = $_GET["id"];
	$diet = $_GET["diet"];
	
	$okRecipes = array();
	$vegRecipes = array();
	if ($diet !== "all") {
		// Run through recipe categories
		if ($diet = "veg") { 	// Vegetarian
			$vegRecipeIDs = $recipeDAL->getRecipeCategories(5);
		} else { 				// Vegan
			$vegRecipeIDs = $recipeDAL->getRecipeCategories(6);
		}
		foreach($vegRecipeIDs as $vegID) {
			foreach ($savedRecipes as $rec) {
				if ($rec["recipeID"] == $vegID) {
					$vegRecipes[] = $rec;
				}
			}
		}
	}
	$dislikedIds = $recipeDAL->getUserComments($id, "dislike");
	if ($diet == "all") {
		foreach($savedRecipes as $recipe) {
			$isBad = false;
			foreach ($dislikedIds as $disliked) {
				if ($recipe["recipeID"] == $disliked) {
					$isBad = true;
				}
			}
			if (!$isBad) {
				$okRecipes[] = $recipe;
			}
		}
	} else {
		foreach($vegRecipes as $recipe) {
			$isBad = false;
			foreach ($dislikedIds as $disliked) {
				if ($recipe["recipeID"] == $disliked) {
					$isBad = true;
				}
			}
			if (!$isBad) {
				$okRecipes[] = $recipe;
			}
		}
	}
	$randomIndex = array_rand($okRecipes);
	$randomRecipe = $okRecipes[$randomIndex];
	if ($randomRecipe["recipeID"] != "") {
		echo $randomRecipe["recipeID"];
	} else {
		echo "Error";
	}
} else if ($_GET["funct"] == "getRecipeByID") {
	try {
		$recipeID = $_GET["recipeID"];
		$recipe = $recipeDAL->getRecipeByID($recipeID);
		if ($recipe["title"] != "") {
			$recipe["ingredients"] = $recipeDAL->getIngredients($recipeID);
						
			echo json_encode($recipe);
		} else {
			echo "Error";
		}
		
	} catch (Exception $e) {
		echo "Error";
	}
	
	
} else if ($_POST["funct"] == "recipeUserComment") {
	$id = $_POST["id"];
	$recipeID = $_POST["recipeID"];
	$userComment = $_POST["comment"];
	$savedRecipe = "";
	if (isset($_SESSION["accessToken"]) &&
		isset($_POST["accessToken"]) &&
		$_POST["accessToken"] == $_SESSION["accessToken"]) {
			
		foreach ($savedRecipes as $recipe) {
			if ($recipe["recipeID"] == $recipeID) {
				$savedRecipe = $recipe;
			}
		}
		if ($savedRecipe["title"] != "") {
			$comment = $recipeDAL->getRecipeComment($id, $recipeID);
			
			if ($comment == "") {
				echo $comment;
				$recipeDAL->addComment($id, $savedRecipe["recipeID"], $userComment);
				echo $savedRecipe["title"];
			} else {
				echo "Error";
			}
		} else {
			echo "Error";
		}
	} else {
		echo "Error";
	}
} else if ($_POST["funct"] == "recipeUserRemoveComment") {
	$id = $_POST["id"];
	$recipeID = $_POST["recipeID"];
	if (isset($_SESSION["accessToken"]) &&
		isset($_POST["accessToken"]) &&
		$_POST["accessToken"] == $_SESSION["accessToken"]) {
			
		try {
			$recipeDAL->deleteComment($id, $recipeID);
			echo "Deleted";
		} catch (Exception $e){
			echo "Error";
		}
	} else {
		echo "Error";
	}
} else if ($_POST["funct"] == "recipeGetComments") {
	$id = $_POST["id"];
	$comment = $_POST["comment"];
	if (isset($_SESSION["accessToken"]) &&
		isset($_POST["accessToken"]) &&
		$_POST["accessToken"] == $_SESSION["accessToken"]) {
			
		$commentedIDs = $recipeDAL->getUserComments($id, $comment);
		if (count($commentedIDs) > 0) {
			$list = array();
			foreach ($commentedIDs as $commented) {
				foreach ($savedRecipes as $recipe) {
					if ($recipe["recipeID"] == $commented) {
						$list[] = $recipe;			
					}
				}
			}
			echo json_encode($list);
		} else {
			echo "NoneFound";
		}
	} else {
		echo "Error";
	}
} 

function getPageFromURL($url) {
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);
	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);

	$output = curl_exec($curl);
	curl_close($curl);

	return $output;
}
function getQuery($page, $query) {
	$dom = new DOMDocument("1.0", "utf-8");
	$page = mb_convert_encoding($page, 'utf-8', mb_detect_encoding($page));
	$page = mb_convert_encoding($page, 'html-entities', 'utf-8'); 
	
	if ($dom->loadHTML($page)) { 
		$x = new DOMXPath($dom);
		return $x->query($query);
	}

	throw new Exception("Could not load HTML from page");
}

