// /fe_velvue/eslint.config.js
import js from "@eslint/js"; // Provides ESLint's recommended rules
import pluginVue from "eslint-plugin-vue";
import globals from "globals";
import vueEslintConfigPrettier from "@vue/eslint-config-prettier"; // Integrates Prettier rules

export default [
  // 1. Global ignores
  {
    ignores: [
      "**/node_modules/",
      "**/dist/",
      ".gitignore",
      "public/", // Usually, static assets in public don't need linting
      // Add any other specific files or directories you want ESLint to ignore
    ],
  },

  // 2. ESLint's recommended built-in rules
  js.configs.recommended,

  // 3. Vue 3 recommended rules from eslint-plugin-vue
  // This includes vue-eslint-parser and sets it up for .vue files.
  ...pluginVue.configs["flat/recommended"],

  // 4. Customizations for your project's Vue and JavaScript files
  {
    files: ["src/**/*.{js,vue,mjs}"], // Target files within your src directory
    languageOptions: {
      sourceType: "module", // Your project uses ES modules
      ecmaVersion: "latest", // Use the latest ECMAScript features
      globals: {
        ...globals.browser, // For code running in the browser (Vue components, etc.)
        // defineProps, defineEmits, etc. are auto-recognized by eslint-plugin-vue with modern vue-eslint-parser
        // No need to add them here typically.
      },
    },
    rules: {
      // Add or override any ESLint (not Prettier/formatting) rules here.
      // For example, if you don't want to enforce multi-word component names:
      // "vue/multi-word-component-names": "off",
      // Example: disallow console.log in production
      // 'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    },
  },

  // 5. Configuration for your JavaScript/MJS configuration files (Node.js environment)
  // These files are at the root of your fe_velvue project.
  {
    files: ["vite.config.mjs", "eslint.config.mjs"],
    languageOptions: {
      sourceType: "module", // These are ES Modules
      globals: {
        ...globals.node, // Add Node.js global variables
      },
    },
    rules: {
      // Specific rules for your config files, if any
    },
  },
  {
    files: ["tailwind.config.js", "postcss.config.js"], // These are CommonJS modules
    languageOptions: {
      sourceType: "commonjs", // Specify CommonJS for these files
      globals: {
        ...globals.node, // Add Node.js global variables (module, require, etc.)
      },
    },
    rules: {
      // Specific rules for your CJS config files, if any
    },
  },

  // 6. Apply Prettier configuration
  // This MUST be the LAST item in the array.
  // It disables ESLint rules that would conflict with Prettier's formatting.
  vueEslintConfigPrettier,
];
