<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

// IDE Theme Colors
const theme = {
  background: '#0d1117',
  surface: '#161b22',
  border: '#30363d',
  text: '#f0f6fc',
  textSecondary: '#8b949e',
  accent: '#58a6ff',
  success: '#3fb950',
  warning: '#d29922',
  error: '#f85149',
  purple: '#a5a3ff',
  orange: '#ffa657',
  green: '#7ce38b'
};

// Personal Information
const personalInfo = {
  name: 'Jarrel Mangahas',
  title: 'Software Engineer II',
  location: 'San Jose City, Nueva Ecija',
  email: 'jarrelmangahas12@gmail.com',
  phone: '+6392109018050',
  linkedin: 'https://www.linkedin.com/in/jarrel-mangahas-294174171/',
  website: 'jrlmnghs.online'
};

// Experience Data
const experience = [
  {
    company: 'YNS Philippines',
    position: 'Software Engineer II',
    period: 'Jul 2019 - Sept 2025',
    location: 'Makati City, Philippines',
    achievements: [
      'Led development of multiple enterprise-level applications including banking software, job portals, online booking, and mobile apps',
      'Improved system performance by 30% through query optimization and debugging of legacy systems',
      'Implemented SCRUM methodology across projects, resulting in faster sprint delivery and enhanced team productivity',
      'Conducted code reviews and mentoring for junior developers, improving overall code quality and reducing bugs',
      'Coordinated with Japanese stakeholders to ensure accurate requirement translation and seamless project delivery'
    ]
  },
  {
    company: 'IQVIA Philippines',
    position: 'Sr. Production Associate',
    period: '2019',
    location: 'Taguig City, Philippines',
    achievements: [
      'Processed and validated daily, weekly, and monthly healthcare data for multinational clients',
      'Ensured data accuracy and compliance, contributing to reliable analytics for decision-making',
      'Collaborated with cross-functional teams to streamline reporting workflows'
    ]
  },
  {
    company: 'Ra Omega Cable TV Corporation',
    position: 'Network Administrator',
    period: '2018 - 2019',
    location: 'San Jose, Philippines',
    achievements: [
      'Developed and deployed in-house inventory and appointment systems, reducing manual work by 40%',
      'Maintained network infrastructure and hardware, ensuring 99.9% uptime',
      'Automated reporting processes, cutting down data preparation time from hours to minutes'
    ]
  }
];

// Skills Data
const skills = {
  languages: ['PHP', 'Laravel', 'Vue.js', 'React.js', 'JavaScript', 'Python', 'Java', 'MySQL', 'Bash'],
  tools: ['Git', 'Docker', 'AWS', 'Agile', 'SCRUM', 'API Design', 'REST', 'RSA', 'Firebase', 'Shopify'],
  methodologies: ['Agile', 'SCRUM', 'Fast Learner', 'API Design', 'REST', 'Query Optimization', 'Debugging', 'Automation']
};

// Education
const education = {
  degree: 'Bachelor of Science in Information Technology (BSIT)',
  school: 'Central Luzon State University',
  period: '2010 - 2018',
  location: 'Constancio Padilla National High School | 2010 - 2014'
};

// Terminal-like typing animation
const terminalText = ref('');
const isTyping = ref(false);
const currentLine = ref(0);

const terminalLines = [
  '$ whoami',
  'Jarrel Mangahas - Software Engineer II',
  '',
  '$ cat experience.txt',
  '5+ years in full-stack development',
  'Expert in PHP, Laravel, Vue.js, React.js',
  'Proven track record in system optimization',
  '',
  '$ ls skills/',
  'Backend: PHP, Laravel, MySQL, Python',
  'Frontend: Vue.js, React.js, JavaScript',
  'DevOps: Docker, AWS, Git',
  'Methodologies: Agile, SCRUM',
  '',
  '$ ./contact.sh',
  'Ready to build something amazing together?'
];

const typeTerminalText = async () => {
  isTyping.value = true;
  terminalText.value = '';
  
  for (let i = 0; i < terminalLines.length; i++) {
    const line = terminalLines[i];
    currentLine.value = i;
    
    if (line === '') {
      terminalText.value += '\n';
      await new Promise(resolve => setTimeout(resolve, 200));
      continue;
    }
    
    for (let j = 0; j < line.length; j++) {
      terminalText.value += line[j];
      await new Promise(resolve => setTimeout(resolve, 30));
    }
    
    terminalText.value += '\n';
    await new Promise(resolve => setTimeout(resolve, 500));
  }
  
  isTyping.value = false;
};

onMounted(() => {
  typeTerminalText();
});
</script>


<template>
  <Head title="Jarrel Mangahas | Software Engineer" />
  <div class="min-h-screen" :style="{ backgroundColor: theme.background, color: theme.text }">
    <!-- IDE Header Bar -->
    <div class="flex items-center justify-between px-4 py-2 border-b" :style="{ borderColor: theme.border, backgroundColor: theme.surface }">
      <div class="flex items-center space-x-2">
        <div class="flex space-x-1">
          <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: theme.error }"></div>
          <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: theme.warning }"></div>
          <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: theme.success }"></div>
        </div>
        <span class="text-sm font-mono ml-4" :style="{ color: theme.textSecondary }">jarrel-mangahas.dev</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="text-xs font-mono" :style="{ color: theme.textSecondary }">Software Engineer II</span>
        <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: theme.success }"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex">
      <!-- Sidebar -->
      <div class="w-64 border-r min-h-screen" :style="{ borderColor: theme.border, backgroundColor: theme.surface }">
        <div class="p-4">
          <h3 class="text-sm font-semibold mb-4" :style="{ color: theme.text }">EXPLORER</h3>
          <div class="space-y-1">
            <div class="flex items-center space-x-2 py-1 px-2 rounded cursor-pointer hover:bg-opacity-20" 
                 :style="{ backgroundColor: theme.accent + '20' }">
              <span class="text-xs">📁</span>
              <span class="text-sm font-mono">jarrel-mangahas</span>
            </div>
            <div class="ml-4 space-y-1">
              <div class="flex items-center space-x-2 py-1 px-2 rounded cursor-pointer hover:bg-opacity-20" 
                   :style="{ backgroundColor: theme.accent + '20' }">
                <span class="text-xs">📄</span>
                <span class="text-sm font-mono">experience.js</span>
              </div>
              <div class="flex items-center space-x-2 py-1 px-2 rounded cursor-pointer hover:bg-opacity-20" 
                   :style="{ backgroundColor: theme.accent + '20' }">
                <span class="text-xs">📄</span>
                <span class="text-sm font-mono">skills.json</span>
              </div>
              <div class="flex items-center space-x-2 py-1 px-2 rounded cursor-pointer hover:bg-opacity-20" 
                   :style="{ backgroundColor: theme.accent + '20' }">
                <span class="text-xs">📄</span>
                <span class="text-sm font-mono">education.md</span>
              </div>
              <div class="flex items-center space-x-2 py-1 px-2 rounded cursor-pointer hover:bg-opacity-20" 
                   :style="{ backgroundColor: theme.accent + '20' }">
                <span class="text-xs">📄</span>
                <span class="text-sm font-mono">contact.sh</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Editor Area -->
      <div class="flex-1">
        <!-- Terminal Section -->
        <div class="border-b" :style="{ borderColor: theme.border }">
          <div class="flex items-center px-4 py-2" :style="{ backgroundColor: theme.surface }">
            <span class="text-sm font-mono" :style="{ color: theme.textSecondary }">TERMINAL</span>
          </div>
          <div class="p-4 font-mono text-sm" :style="{ backgroundColor: theme.background }">
            <pre class="whitespace-pre-wrap" :style="{ color: theme.text }">{{ terminalText }}</pre>
            <span v-if="isTyping" class="inline-block w-2 h-4 ml-1 animate-pulse" :style="{ backgroundColor: theme.accent }"></span>
          </div>
        </div>

        <!-- Content Tabs -->
        <div class="flex border-b" :style="{ borderColor: theme.border }">
          <div class="px-4 py-2 border-r cursor-pointer" :style="{ borderColor: theme.border, backgroundColor: theme.surface }">
            <span class="text-sm font-mono" :style="{ color: theme.text }">experience.js</span>
          </div>
          <div class="px-4 py-2 border-r cursor-pointer" :style="{ borderColor: theme.border }">
            <span class="text-sm font-mono" :style="{ color: theme.textSecondary }">skills.json</span>
          </div>
          <div class="px-4 py-2 border-r cursor-pointer" :style="{ borderColor: theme.border }">
            <span class="text-sm font-mono" :style="{ color: theme.textSecondary }">education.md</span>
          </div>
          <div class="px-4 py-2 cursor-pointer" :style="{ borderColor: theme.border }">
            <span class="text-sm font-mono" :style="{ color: theme.textSecondary }">contact.sh</span>
          </div>
        </div>

        <!-- Experience Section -->
        <div class="p-6">
          <div class="max-w-4xl">
            <!-- Personal Info Header -->
            <div class="mb-8 p-6 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
              <h1 class="text-3xl font-bold mb-2" :style="{ color: theme.text }">{{ personalInfo.name }}</h1>
              <h2 class="text-xl mb-4" :style="{ color: theme.accent }">{{ personalInfo.title }}</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" :style="{ color: theme.textSecondary }">
                <div class="flex items-center space-x-2">
                  <span>📍</span>
                  <span>{{ personalInfo.location }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span>📧</span>
                  <span>{{ personalInfo.email }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span>📱</span>
                  <span>{{ personalInfo.phone }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span>🌐</span>
                  <span>{{ personalInfo.website }}</span>
                </div>
              </div>
            </div>

            <!-- Experience -->
            <div class="mb-8">
              <h3 class="text-2xl font-bold mb-6 flex items-center" :style="{ color: theme.text }">
                <span class="mr-2">💼</span>
                Professional Experience
              </h3>
              <div class="space-y-6">
                <div v-for="(job, index) in experience" :key="index" 
                     class="p-6 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
                  <div class="flex justify-between items-start mb-4">
                    <div>
                      <h4 class="text-xl font-semibold" :style="{ color: theme.text }">{{ job.position }}</h4>
                      <p class="text-lg" :style="{ color: theme.accent }">{{ job.company }}</p>
                      <p class="text-sm" :style="{ color: theme.textSecondary }">{{ job.location }}</p>
                    </div>
                    <span class="text-sm font-mono px-3 py-1 rounded" :style="{ backgroundColor: theme.accent + '20', color: theme.accent }">
                      {{ job.period }}
                    </span>
                  </div>
                  <ul class="space-y-2">
                    <li v-for="(achievement, idx) in job.achievements" :key="idx" 
                        class="flex items-start space-x-2 text-sm" :style="{ color: theme.textSecondary }">
                      <span class="mt-1" :style="{ color: theme.success }">▶</span>
                      <span>{{ achievement }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Skills -->
            <div class="mb-8">
              <h3 class="text-2xl font-bold mb-6 flex items-center" :style="{ color: theme.text }">
                <span class="mr-2">🛠️</span>
                Technical Skills
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-4 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
                  <h4 class="font-semibold mb-3" :style="{ color: theme.purple }">Languages & Frameworks</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="skill in skills.languages" :key="skill"
                          class="px-3 py-1 text-xs rounded-full font-mono" 
                          :style="{ backgroundColor: theme.purple + '20', color: theme.purple }">
                      {{ skill }}
                    </span>
                  </div>
                </div>
                <div class="p-4 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
                  <h4 class="font-semibold mb-3" :style="{ color: theme.orange }">Tools & Technologies</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="tool in skills.tools" :key="tool"
                          class="px-3 py-1 text-xs rounded-full font-mono" 
                          :style="{ backgroundColor: theme.orange + '20', color: theme.orange }">
                      {{ tool }}
                    </span>
                  </div>
                </div>
                <div class="p-4 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
                  <h4 class="font-semibold mb-3" :style="{ color: theme.green }">Methodologies</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="method in skills.methodologies" :key="method"
                          class="px-3 py-1 text-xs rounded-full font-mono" 
                          :style="{ backgroundColor: theme.green + '20', color: theme.green }">
                      {{ method }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Education -->
            <div class="mb-8">
              <h3 class="text-2xl font-bold mb-6 flex items-center" :style="{ color: theme.text }">
                <span class="mr-2">🎓</span>
                Education
              </h3>
              <div class="p-6 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
                <h4 class="text-xl font-semibold mb-2" :style="{ color: theme.text }">{{ education.degree }}</h4>
                <p class="text-lg mb-2" :style="{ color: theme.accent }">{{ education.school }}</p>
                <p class="text-sm" :style="{ color: theme.textSecondary }">{{ education.period }}</p>
              </div>
            </div>

            <!-- Contact CTA -->
            <div class="text-center p-8 rounded-lg border" :style="{ backgroundColor: theme.surface, borderColor: theme.border }">
              <h3 class="text-2xl font-bold mb-4" :style="{ color: theme.text }">Ready to build something amazing?</h3>
              <p class="mb-6" :style="{ color: theme.textSecondary }">Let's connect and discuss how I can help bring your ideas to life.</p>
              <div class="flex justify-center space-x-4">
                <a :href="personalInfo.linkedin" target="_blank" 
                   class="px-6 py-3 rounded-lg font-semibold transition-all duration-300 hover:scale-105"
                   :style="{ backgroundColor: theme.accent, color: theme.background }">
                  Connect on LinkedIn
                </a>
                <a :href="`mailto:${personalInfo.email}`" 
                   class="px-6 py-3 rounded-lg font-semibold border transition-all duration-300 hover:scale-105"
                   :style="{ borderColor: theme.accent, color: theme.accent }">
                  Send Email
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* IDE Theme Styles */
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600;700&display=swap');

* {
  font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
}

/* Custom scrollbar for IDE feel */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #0d1117;
}

::-webkit-scrollbar-thumb {
  background: #30363d;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #484f58;
}

/* Terminal cursor animation */
@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

.terminal-cursor {
  animation: blink 1s infinite;
}

/* Hover effects for interactive elements */
.hover-lift {
  transition: transform 0.2s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-2px);
}

/* Code syntax highlighting colors */
.keyword { color: #ff7b72; }
.string { color: #a5d6ff; }
.comment { color: #8b949e; }
.function { color: #d2a8ff; }
.variable { color: #ffa657; }

/* IDE-style focus states */
.focus-ring:focus {
  outline: 2px solid #58a6ff;
  outline-offset: 2px;
}

/* Responsive design */
@media (max-width: 768px) {
  .sidebar {
    display: none;
  }
  
  .main-content {
    width: 100%;
  }
}

/* Animation for typing effect */
@keyframes typewriter {
  from { width: 0; }
  to { width: 100%; }
}

.typewriter {
  overflow: hidden;
  border-right: 2px solid #58a6ff;
  white-space: nowrap;
  animation: typewriter 2s steps(40, end);
}

/* Glow effect for accent elements */
.glow {
  box-shadow: 0 0 20px rgba(88, 166, 255, 0.3);
}

/* Terminal-style selection */
::selection {
  background: #58a6ff;
  color: #0d1117;
}

/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}
</style>

