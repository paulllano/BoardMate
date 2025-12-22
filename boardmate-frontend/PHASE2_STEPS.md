# Phase 2: Nuxt.js Setup - Step by Step

## Current Location
You're in: `C:\Users\paulm\Desktop\BOARDMATE (REAL)\boardmate-frontend`

## Step 1: Create Nuxt.js Project

Run this command in your terminal (PowerShell):

```bash
npx nuxi@latest init .
```

**Note:** The `.` means "create in current directory" - this will set up Nuxt.js in the `boardmate-frontend` folder.

**What happens:**
- You'll be asked: "Package manager?" - Choose `npm` (press Enter)
- Nuxt.js will install all dependencies
- This may take a few minutes

---

## Step 2: Install Tailwind CSS

After Nuxt.js is installed, run:

```bash
npm install -D tailwindcss postcss autoprefixer
```

Then initialize Tailwind:

```bash
npx tailwindcss init
```

---

## Step 3: Configure Tailwind

### 3.1 Update `tailwind.config.js`

Open `tailwind.config.js` and replace with:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./error.vue"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### 3.2 Create CSS File

Create file: `assets/css/main.css`

Content:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### 3.3 Update `nuxt.config.ts`

Add the CSS file:

```typescript
export default defineNuxtConfig({
  devtools: { enabled: true },
  css: ['~/assets/css/main.css'],
})
```

---

## Step 4: Test

Run:
```bash
npm run dev
```

Open: http://localhost:3000

---

## Need Help?

If you encounter any errors, let me know and I'll help fix them!

