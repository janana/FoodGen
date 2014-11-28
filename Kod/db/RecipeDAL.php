<?php

class RecipeDAL extends DAL {
	/**
	 * @param string title
	 * @param string pic-url
	 * @param string portions
	 * @param string instructions
	 */
	public function addRecipe($title, $pic, $portions, $instruction) {
		$sql = "INSERT INTO Recipe VALUES (DEFAULT, ?, ?, ?, ?)";
		
		$prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("ssss", $title, $pic, $portions, $instruction);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }
		return $this->connection->insert_id;
	}
	/**
	 * @param int recipeID
	 * @param int categoryID
	 */
	public function addCategory($recipeID, $categoryID) {
		$sql = "INSERT INTO RecipeCategory VALUES (?, ?)";
		
		$prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("ii", $recipeID, $categoryID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }
	}
	
	/**
	 * @param int recipeID
	 * @param string name
	 * @param string amount
	 */
	public function addIngredient($recipeID, $ingredient) {
		$sql = "INSERT INTO Ingredient VALUES (DEFAULT, ?, ?)";
		
		$prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("is", $recipeID, $ingredient);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }
	}
	
	public function addComment($userID, $recipeID, $comment) {
		$sql = "INSERT INTO RecipeUserComment VALUES (DEFAULT, ?, ?, ?)";
		
		$prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("isi", $userID, $comment, $recipeID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }
	}
	
	
	
	/**
	 * @return Array of recipes
	 */
	public function getRecipes() {
		$sql = "SELECT * FROM Recipe";
        $prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $exec = $prep->bind_result($recipeID, $title, $pic, $portions, $instruction);
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $return = array();

	    while ($prep->fetch()) {
	        $return[] = array("recipeID" => $recipeID,
							"title" => $title,
							"pic" => $pic,
							"portions" => $portions,
							"instruction" => $instruction);
	 	}
        return $return;
	}
	public function getRecipeByID($recipeID) {
		$sql = "SELECT * FROM Recipe WHERE RecipeID = ?";
        $prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("i", $recipeID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $exec = $prep->bind_result($recipeID, $title, $pic, $portions, $instruction);
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }
		$return = "";
	    while ($prep->fetch()) {
	        $return = array("recipeID" => $recipeID,
							"title" => $title,
							"pic" => $pic,
							"portions" => $portions,
							"instruction" => $instruction);
	 	}
        return $return;
	}
	
	
	/**
	 * @return Array of recipeIDs with category from categoryID
	 */
	public function getRecipeCategories($categoryID) {
		$sql = "SELECT RecipeID FROM RecipeCategory WHERE CategoryID = ?";
        $prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("i", $categoryID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $exec = $prep->bind_result($recipeID);
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $return = array();

	    while ($prep->fetch()) {
	        $return[] = $recipeID;
	 	}
        return $return;
	}
	/**
	 * @return Array of ingredients
	 */
	public function getIngredients($recipeID) {
		$sql = "SELECT Ingredient FROM Ingredient WHERE RecipeID = ?";
        $prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("i", $recipeID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $exec = $prep->bind_result($ingredient);
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $return = array();

	    while ($prep->fetch()) {
	        $return[] = $ingredient;
	 	}
        return $return;
	}
	
	/**
	 * @return Array of recipeID's
	 */
	public function getUserComments($userID, $comment) {
		$sql = "SELECT RecipeID FROM RecipeUserComment WHERE UserID = ? && Comment = ?";
        $prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("is", $userID, $comment);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $exec = $prep->bind_result($recipeID);
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $return = array();

	    while ($prep->fetch()) {
	        $return[] = $recipeID;
	 	}
        return $return;
	}
	/**
	 * @return string comment
	 */
	public function getRecipeComment($userID, $recipeID) {
		$sql = "SELECT Comment FROM RecipeUserComment WHERE UserID = ? && RecipeID = ?";
        $prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("ii", $userID, $recipeID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $exec = $prep->bind_result($comment);
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }

        $return = "";

	    while ($prep->fetch()) {
	        $return = $comment;
	 	}
        return $return;
	}
	
	/**
	 * @param int userID
	 * @param int recipeID
	 */
	public function deleteComment($userID, $recipeID) {
		$sql = "DELETE FROM RecipeUserComment WHERE UserID = ? && RecipeID = ?";
		
		$prep = $this->connection->prepare($sql);
        if ($prep == false) {
            throw new Exception("prepare of [$sql] failed " . $this->connection->error);
        }
        $exec = $prep->bind_param("ii", $userID, $recipeID);
		if ($exec == false) {
			throw new Exception("Bind param of [$sql] failed " . $prep->error);
		}
        $exec = $prep->execute();
        if ($exec == false) {
        	throw new Exception("execute of [$sql] failed " . $prep->error);
        }
	}
	
	
}