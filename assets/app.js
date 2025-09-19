import { registerReactControllerComponents } from '@symfony/ux-react';
import './bootstrap';
import './styles/app.css';

registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));

console.log('UX React components registered');

const context = require.context('./react/controllers', true, /\.(j|t)sx?$/);
context.keys().forEach(key => {
  console.log('Found component:', key);
});

import React from 'react';
import { createRoot } from 'react-dom/client';

document.addEventListener('DOMContentLoaded', () => {
  const uxComponents = document.querySelectorAll('[data-react-component]');
  console.log('Found UX React components:', uxComponents.length);   
  const containers = document.querySelectorAll('[data-react-component="WeatherApp"]');
  containers.forEach(container => {
    if (container && !container.hasAttribute('data-react-controlled')) {
      try {
        import('./react/controllers/WeatherApp.jsx').then(WeatherApp => {
          const root = createRoot(container);
          root.render(React.createElement(WeatherApp.default));
          container.setAttribute('data-react-controlled', 'true');
          console.log('WeatherApp explicitly mounted as fallback');
        });
      } catch (error) {
        console.error('Error mounting WeatherApp:', error);
      }
    }
  });
});