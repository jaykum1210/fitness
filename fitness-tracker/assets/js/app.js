function toggleMenu() {
    const mobileNav = document.getElementById('mobileNav');
    mobileNav.classList.toggle('show');
}

const motivationalQuotes = [
    "The only bad workout is the one that didn't happen.",
    "Your body can stand almost anything. It's your mind you have to convince.",
    "Take care of your body. It's the only place you have to live.",
    "Fitness is not about being better than someone else. It's about being better than you used to be.",
    "The pain you feel today will be the strength you feel tomorrow.",
    "Don't stop when you're tired. Stop when you're done.",
    "Success starts with self-discipline.",
    "Your health is an investment, not an expense."
];

const surpriseTips = [
    { type: "💡 Tip", content: "Drink a glass of water before every meal to aid digestion and control appetite." },
    { type: "🏃 Exercise", content: "Try 30 jumping jacks right now! Quick cardio boost." },
    { type: "🎯 Challenge", content: "Hold a plank for 60 seconds. You got this!" },
    { type: "💪 Motivation", content: "Remember: Progress, not perfection. Every step counts!" },
    { type: "🥗 Nutrition", content: "Add one extra serving of vegetables to your next meal." },
    { type: "😴 Recovery", content: "Quality sleep is crucial. Aim for 7-8 hours tonight." },
    { type: "🧘 Mindset", content: "Take 5 deep breaths. Fitness is mental too." },
    { type: "📈 Goal", content: "Set one small fitness goal for this week and crush it!" }
];

function rotateQuote() {
    const quoteElement = document.getElementById('motivationalQuote');
    if (quoteElement) {
        const randomQuote = motivationalQuotes[Math.floor(Math.random() * motivationalQuotes.length)];
        quoteElement.textContent = `"${randomQuote}"`;
    }
}

function surpriseMe() {
    const resultElement = document.getElementById('surpriseResult');
    const surprise = surpriseTips[Math.floor(Math.random() * surpriseTips.length)];
    
    resultElement.innerHTML = `
        <h3>${surprise.type}</h3>
        <p style="font-size: 1.125rem; margin-top: 1rem;">${surprise.content}</p>
    `;
    resultElement.classList.add('show');
}

function calculateBMI() {
    const height = parseFloat(document.getElementById('bmiHeight').value);
    const weight = parseFloat(document.getElementById('bmiWeight').value);
    
    if (!height || !weight) {
        alert('Please enter both height and weight');
        return;
    }
    
    const bmi = (weight / ((height/100) ** 2)).toFixed(1);
    let category = '';
    
    if (bmi < 18.5) category = 'Underweight';
    else if (bmi < 25) category = 'Normal weight';
    else if (bmi < 30) category = 'Overweight';
    else category = 'Obese';
    
    const resultElement = document.getElementById('bmiResult');
    resultElement.innerHTML = `
        <h3>${bmi}</h3>
        <p>Category: ${category}</p>
    `;
    resultElement.classList.add('show');
}

function calculateCalories() {
    const age = parseInt(document.getElementById('calAge').value);
    const weight = parseFloat(document.getElementById('calWeight').value);
    const height = parseFloat(document.getElementById('calHeight').value);
    const gender = document.getElementById('calGender').value;
    const activity = parseFloat(document.getElementById('calActivity').value);
    
    if (!age || !weight || !height) {
        alert('Please fill in all fields');
        return;
    }
    
    let bmr;
    if (gender === 'male') {
        bmr = 10 * weight + 6.25 * height - 5 * age + 5;
    } else {
        bmr = 10 * weight + 6.25 * height - 5 * age - 161;
    }
    
    const dailyCalories = Math.round(bmr * activity);
    
    const resultElement = document.getElementById('caloriesResult');
    resultElement.innerHTML = `
        <h3>${dailyCalories} cal/day</h3>
        <p>Daily calorie needs to maintain weight</p>
        <p style="margin-top: 1rem; font-size: 0.875rem;">
            For weight loss: ${dailyCalories - 500} cal/day<br>
            For weight gain: ${dailyCalories + 500} cal/day
        </p>
    `;
    resultElement.classList.add('show');
}

function calculateWater() {
    const weight = parseFloat(document.getElementById('waterWeight').value);
    const activity = document.getElementById('waterActivity').value;
    
    if (!weight) {
        alert('Please enter your weight');
        return;
    }
    
    let waterIntake = weight * 0.033;
    
    if (activity === 'moderate') waterIntake += 0.5;
    else if (activity === 'high') waterIntake += 1;
    
    const resultElement = document.getElementById('waterResult');
    resultElement.innerHTML = `
        <h3>${waterIntake.toFixed(1)} liters/day</h3>
        <p>Recommended daily water intake</p>
        <p style="margin-top: 1rem; font-size: 0.875rem;">
            That's about ${Math.round(waterIntake * 4)} glasses (250ml each)
        </p>
    `;
    resultElement.classList.add('show');
}

function filterWorkouts(category) {
    const allCards = document.querySelectorAll('.workout-card');
    const allTabs = document.querySelectorAll('.tab-btn');
    
    allTabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    allCards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function searchWorkouts() {
    const searchTerm = document.getElementById('workoutSearch').value.toLowerCase();
    const allCards = document.querySelectorAll('.workout-card');
    
    allCards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || desc.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

if (document.getElementById('motivationalQuote')) {
    rotateQuote();
    setInterval(rotateQuote, 10000);
}

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                setTimeout(() => btn.disabled = false, 1000);
            }
        });
    });
});