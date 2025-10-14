<?php 
$pageTitle = 'Nutrition - Fitness Tracker';
include '../includes/header.php';

$recipes = readJSON(DATA_DIR . 'recipes.json');
$selectedCategory = $_GET['category'] ?? 'all';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">🥗 Nutrition & Meal Plans</h1>
        <p style="text-align: center; color: var(--text-light); max-width: 700px; margin: 0 auto 3rem;">
            Fuel your fitness journey with healthy, delicious recipes. All meals include macro breakdowns to help you hit your goals.
        </p>

        <div class="nutrition-intro cards-grid" style="margin-bottom: 3rem;">
            <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <div class="card-icon">🍎</div>
                <h3 style="color: white;">Balanced Nutrition</h3>
                <p style="opacity: 0.95;">All recipes designed with optimal protein, carbs, and healthy fats.</p>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                <div class="card-icon">⏱️</div>
                <h3 style="color: white;">Quick & Easy</h3>
                <p style="opacity: 0.95;">Most meals ready in under 30 minutes - perfect for busy schedules.</p>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                <div class="card-icon">💪</div>
                <h3 style="color: white;">Performance Fuel</h3>
                <p style="opacity: 0.95;">High-protein options to support muscle growth and recovery.</p>
            </div>
        </div>

        <div class="workout-tabs" style="margin-bottom: 2rem;">
            <a href="?category=all" class="tab-btn <?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">All Recipes</a>
            <a href="?category=breakfast" class="tab-btn <?php echo $selectedCategory === 'breakfast' ? 'active' : ''; ?>">Breakfast</a>
            <a href="?category=lunch" class="tab-btn <?php echo $selectedCategory === 'lunch' ? 'active' : ''; ?>">Lunch</a>
            <a href="?category=dinner" class="tab-btn <?php echo $selectedCategory === 'dinner' ? 'active' : ''; ?>">Dinner</a>
            <a href="?category=snack" class="tab-btn <?php echo $selectedCategory === 'snack' ? 'active' : ''; ?>">Snacks</a>
        </div>

        <div class="cards-grid">
            <?php foreach ($recipes as $recipe): ?>
                <?php if ($selectedCategory === 'all' || $recipe['category'] === $selectedCategory): ?>
                <div class="card recipe-card">
                    <div class="recipe-icon">
                        <?php 
                        $icons = ['breakfast' => '🥞', 'lunch' => '🥗', 'dinner' => '🍽️', 'snack' => '🍇'];
                        echo $icons[$recipe['category']];
                        ?>
                    </div>
                    <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
                    <div class="recipe-badges">
                        <span class="badge-pill"><?php echo ucfirst($recipe['category']); ?></span>
                        <span class="badge-pill">⏱️ <?php echo $recipe['prepTime']; ?> min</span>
                    </div>
                    
                    <div class="macro-grid">
                        <div class="macro-item">
                            <div class="macro-value"><?php echo $recipe['calories']; ?></div>
                            <div class="macro-label">Calories</div>
                        </div>
                        <div class="macro-item">
                            <div class="macro-value"><?php echo $recipe['protein']; ?>g</div>
                            <div class="macro-label">Protein</div>
                        </div>
                        <div class="macro-item">
                            <div class="macro-value"><?php echo $recipe['carbs']; ?>g</div>
                            <div class="macro-label">Carbs</div>
                        </div>
                        <div class="macro-item">
                            <div class="macro-value"><?php echo $recipe['fats']; ?>g</div>
                            <div class="macro-label">Fats</div>
                        </div>
                    </div>

                    <div class="recipe-ingredients">
                        <strong>Ingredients:</strong>
                        <ul>
                            <?php foreach (array_slice($recipe['ingredients'], 0, 3) as $ingredient): ?>
                            <li><?php echo $ingredient; ?></li>
                            <?php endforeach; ?>
                            <?php if (count($recipe['ingredients']) > 3): ?>
                            <li>+<?php echo count($recipe['ingredients']) - 3; ?> more...</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <button onclick="showRecipeModal(<?php echo htmlspecialchars(json_encode($recipe)); ?>)" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        View Recipe
                    </button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div id="recipeModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeRecipeModal()">&times;</span>
        <div id="recipeModalContent"></div>
    </div>
</div>

<script>
function showRecipeModal(recipe) {
    const modal = document.getElementById('recipeModal');
    const content = document.getElementById('recipeModalContent');
    
    content.innerHTML = `
        <h2>${recipe.name}</h2>
        <p><strong>Prep Time:</strong> ${recipe.prepTime} minutes</p>
        <p><strong>Nutrition:</strong> ${recipe.calories} cal | ${recipe.protein}g protein | ${recipe.carbs}g carbs | ${recipe.fats}g fats</p>
        <h3>Ingredients:</h3>
        <ul>
            ${recipe.ingredients.map(ing => `<li>${ing}</li>`).join('')}
        </ul>
        <h3>Instructions:</h3>
        <p>${recipe.instructions}</p>
    `;
    
    modal.style.display = 'flex';
}

function closeRecipeModal() {
    document.getElementById('recipeModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('recipeModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>