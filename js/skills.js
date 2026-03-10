// Skills Section JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const skillsSection = document.querySelector('.skills-section');
    
    if (skillsSection && typeof skillsData !== 'undefined') {
        const skillsGrid = document.createElement('div');
        skillsGrid.className = 'skills-grid';
        
        skillsData.forEach(skill => {
            const skillCard = document.createElement('div');
            skillCard.className = 'skill-card';
            skillCard.innerHTML = `
                <div class="skill-icon">${skill.icon}</div>
                <h4 class="skill-name">${skill.name}</h4>
                <p class="skill-level">${skill.level}</p>
            `;
            skillsGrid.appendChild(skillCard);
        });
        
        skillsSection.appendChild(skillsGrid);
    }
});
