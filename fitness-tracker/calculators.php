<?php 
$pageTitle = 'Calculators - Fitness Tracker';
include 'includes/header.php'; 
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Fitness Calculators</h1>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem;">Use our free fitness calculators to understand your health stats.</p>

        <div class="calculator-section" id="bmi">
            <h2>📊 BMI Calculator</h2>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">Calculate your Body Mass Index to check if you're in a healthy weight range.</p>
            
            <div class="form-group">
                <label>Height (cm)</label>
                <input type="number" id="bmiHeight" placeholder="e.g., 175">
            </div>
            
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" id="bmiWeight" placeholder="e.g., 70">
            </div>
            
            <button onclick="calculateBMI()" class="btn btn-primary">Calculate BMI</button>
            
            <div id="bmiResult" class="result-box"></div>
        </div>

        <div class="calculator-section" id="calories">
            <h2>🔥 Calories Calculator</h2>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">Find out how many calories you need per day based on your lifestyle.</p>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" id="calAge" placeholder="e.g., 30">
                </div>
                
                <div class="form-group">
                    <label>Gender</label>
                    <select id="calGender">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" id="calWeight" placeholder="e.g., 70">
                </div>
                
                <div class="form-group">
                    <label>Height (cm)</label>
                    <input type="number" id="calHeight" placeholder="e.g., 175">
                </div>
            </div>
            
            <div class="form-group">
                <label>Activity Level</label>
                <select id="calActivity">
                    <option value="1.2">Sedentary (little or no exercise)</option>
                    <option value="1.375">Lightly active (1-3 days/week)</option>
                    <option value="1.55">Moderately active (3-5 days/week)</option>
                    <option value="1.725">Very active (6-7 days/week)</option>
                    <option value="1.9">Extremely active (athlete level)</option>
                </select>
            </div>
            
            <button onclick="calculateCalories()" class="btn btn-primary">Calculate Calories</button>
            
            <div id="caloriesResult" class="result-box"></div>
        </div>

        <div class="calculator-section" id="water">
            <h2>💧 Water Intake Calculator</h2>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">Calculate how much water you should drink daily based on your weight and activity.</p>
            
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" id="waterWeight" placeholder="e.g., 70">
            </div>
            
            <div class="form-group">
                <label>Activity Level</label>
                <select id="waterActivity">
                    <option value="low">Low (mostly sitting)</option>
                    <option value="moderate">Moderate (regular exercise)</option>
                    <option value="high">High (intense training)</option>
                </select>
            </div>
            
            <button onclick="calculateWater()" class="btn btn-primary">Calculate Water Intake</button>
            
            <div id="waterResult" class="result-box"></div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>