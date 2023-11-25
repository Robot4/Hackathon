<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle recipe deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $recipe_id = $_GET['id'];
    $delete_query = "DELETE FROM recipes WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $recipe_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $recipe_id = $_GET['id'];
    $get_recipe_query = "SELECT * FROM recipes WHERE id = ?";
    $get_recipe_stmt = $conn->prepare($get_recipe_query);
    $get_recipe_stmt->bind_param("i", $recipe_id);
    $get_recipe_stmt->execute();
    $result = $get_recipe_stmt->get_result();

    if ($result->num_rows > 0) {
        $recipeData = $result->fetch_assoc();
        echo json_encode($recipeData);
    } else {
        echo json_encode(array()); // Return an empty array if no recipe is found
    }

    $get_recipe_stmt->close();
    $conn->close();
}
// Handle recipe form submission (add or edit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];
    $duration = $_POST['duration'];

    // Upload and save the image
    $image = uploadImage();

    if ($id) {
        // Edit existing recipe
        $update_query = "UPDATE recipes SET name = ?, ingredients = ?, steps = ?, duration = ?";
        // Include image update only if a new image is uploaded
        if ($image) {
            $update_query .= ", image = ?";
        }
        $update_query .= " WHERE id = ? AND user_id = ?";

        $update_stmt = $conn->prepare($update_query);

        if ($image) {
            $update_stmt->bind_param("ssssisii", $name, $ingredients, $steps, $duration, $image, $id, $user_id);
        } else {
            $update_stmt->bind_param("ssssii", $name, $ingredients, $steps, $duration, $id, $user_id);
        }

        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Add new recipe
        $insert_query = "INSERT INTO recipes (name, ingredients, steps, duration, image, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sssssi", $name, $ingredients, $steps, $duration, $image, $user_id);

        $insert_stmt->execute();
        $insert_stmt->close();
    }

    header("Location: index.php");
    exit();
}

// Fetch recipes for the logged-in user
$recipes_query = "SELECT * FROM recipes WHERE user_id = ?";
$stmt = $conn->prepare($recipes_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

function uploadImage() {
    $target_dir = "recipe_images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);

    // Check if a file was selected for upload
    if ($_FILES["image"]["name"]) {
        // Check if the file has been uploaded successfully
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            return basename($_FILES["image"]["name"]);
        } else {
            // Print an error message if the upload fails
            return null;
        }
    } else {
        // No new image selected, return null
        return null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Management</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</head>
<body>
<a href="logout.php" class="btn btn-dark">Logout</a>

<div class="container">
    <center>
        <h2>My Recipes</h2>
    </center>

    <!-- Display recipes in a table -->
    <button type="button" class="btn btn-primary" id="addRecipeBtn">Ajouter</button>
    <br>
    <br>

    <table class="table table-hover" border="1">
        <thead>
        <tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Ingrédients</th>
            <th>Pas</th>
            <th>Durée (minutes)</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recipes as $recipe): ?>
            <tr>
                <td>
                    <?php if ($recipe['image']): ?>
                        <img src="<?php echo 'recipe_images/' . urlencode($recipe['image']); ?>" alt="Recipe Image" width="50">
                    <?php else: ?>
                        No Image
                    <?php endif; ?>
                </td>
                <td><?php echo $recipe['name']; ?></td>
                <td><?php echo $recipe['ingredients']; ?></td>
                <td><?php echo $recipe['steps']; ?></td>
                <td><?php echo $recipe['duration']; ?></td>

                <td>
                    <button type="button" class="btn btn-secondary" onclick="editRecipe(<?php echo $recipe['id']; ?>);">Edit</button>
                    |
                    <a href="?action=delete&id=<?php echo $recipe['id']; ?>" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div id="addRecipeForm" style="display: none;">
        <h2>Ajouter une nouvelle recette </h2>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="">
            <label for="name">Nom:</label>
            <input type="text" id="name" name="name" required><br>

            <label for="ingredients">Ingredients:</label>
            <textarea id="ingredients" name="ingredients" required></textarea><br>

            <label for="steps">Pas:</label>
            <textarea id="steps" name="steps" required></textarea><br>

            <label for="duration">Duree (minutes):</label>
            <input type="number" id="duration" name="duration" required><br>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image"><br>

            <input type="submit" value="Ajoutter">
            <br>
            <br>
            
        </form>

        <button type="button" class="btn btn-secondary" id="cancelRecipeBtn">Anuller</button>
    </div>

    <div id="editRecipeForm" style="display: none;">
        <h2>Edit Recipe</h2>
        <form id="editForm" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" id="editId" name="id" value="">
            <label for="editName">Name:</label>
            <input type="text" id="editName" name="name" required><br>

            <label for="editIngredients">Ingredients:</label>
            <textarea id="editIngredients" name="ingredients" required></textarea><br>

            <label for="editSteps">Steps:</label>
            <textarea id="editSteps" name="steps" required></textarea><br>

            <label for="editDuration">Duration (minutes):</label>
            <input type="number" id="editDuration" name="duration" required><br>

            <label for="editImage">Image:</label>
            <input type="file" id="editImage" name="image"><br>

            <input type="submit" value="Enregistrer">
        </form>

        <button type="button" class="btn btn-secondary" id="cancelEditBtn">Anuller</button>
    </div>
</div>



<script>
    function editRecipe(recipeId) {
        // Show the edit form
        document.getElementById('addRecipeForm').style.display = 'none';
        document.getElementById('editRecipeForm').style.display = 'block';

        // Fetch existing recipe data using AJAX
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var recipeData = JSON.parse(xhr.responseText);

                // Populate the form fields with the fetched data
                document.getElementById('editId').value = recipeData.id;
                document.getElementById('editName').value = recipeData.name;
                document.getElementById('editIngredients').value = recipeData.ingredients;
                document.getElementById('editSteps').value = recipeData.steps;
                document.getElementById('editDuration').value = recipeData.duration;
            }
        };
        xhr.open("GET", "get_recipe.php?id=" + recipeId, true); // Create get_recipe.php to handle the AJAX request
        xhr.send();
    }

    document.getElementById('addRecipeBtn').addEventListener('click', function () {
        document.getElementById('addRecipeForm').style.display = 'block';
        document.getElementById('editRecipeForm').style.display = 'none';
    });

    // Additional JavaScript code for canceling the edit form
    document.getElementById('cancelRecipeBtn').addEventListener('click', function () {
        document.getElementById('addRecipeForm').style.display = 'none';
        document.getElementById('editRecipeForm').style.display = 'none';
    });
</script>

