import Vue from 'vue';

// Import REAL components
import VSensitiveInput from '~/components/global/ssr/VSensitiveInput';

// Import DUMMY components
import ProjectInfo from './project-info';

// Initialize REAL components
Vue.component(VSensitiveInput.name, VSensitiveInput);

// Initialize DUMMY components
Vue.component(ProjectInfo.name, ProjectInfo);
