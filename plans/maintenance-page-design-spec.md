# UG IRB Portal Maintenance Page Design Specification

## Overview

This document outlines the design specification for the UG IRB Portal maintenance page. The maintenance page will be displayed when the system is under maintenance, providing users with clear information while maintaining brand consistency with the existing portal design.

---

## 1. Brand Alignment

### Color Scheme

| Element | Color | Usage |
|---------|-------|-------|
| **Primary Blue** | `#042c5c` (Royal Blue) | Primary branding, icons, accents |
| **Primary Blue Light** | `#325bc5` / `rgb(34, 74, 177)` | Gradients, hover states |
| **Gold Accent** | `#b29762` (Primary Gold) | Dividers, highlights, call-to-action borders |
| **Gold Light** | `#d69e2e` | Secondary accents |
| **Background** | `#f5f7fb` (Light Gray) | Page background |
| **Text Primary** | `#212529` | Main headings |
| **Text Secondary** | `#495057` | Body text |

### Typography

| Element | Font | Size | Weight |
|---------|------|------|--------|
| **Headings** | 'Segoe UI', Tahoma, Geneva, Verdana | 28-48px | 700 |
| **Body Text** | 'Segoe UI', Tahoma, Geneva, Verdana | 16-18px | 400-500 |
| **Logo Text** | 'Segoe UI' | 24-35px | 700 |

### Logo Usage

- **Primary**: [`ug_logo_white.png`](admin/assets/images/ug_logo_white.png) on dark backgrounds
- **Secondary**: [`ug_logo.png`](admin/assets/images/ug_logo.png) on light backgrounds
- **Combined**: [`ug-nmimr-logo.jpg`](admin/assets/images/ug-nmimr-logo.jpg) for official branding

---

## 2. Layout Structure

### Page Layout

```
┌─────────────────────────────────────────────────┐
│              Maintenance Page Container          │
│  ┌───────────────────────────────────────────┐  │
│  │              Header Section                │  │
│  │  ┌─────┐  ┌───────────────────────────┐    │  │
│  │  │ Logo│  │  UG HARES / NMIMR         │    │  │
│  │  │     │  │  Ethics Portal           │    │  │
│  │  └─────┘  └───────────────────────────┘    │  │
│  └───────────────────────────────────────────┘  │
│                                                  │
│  ┌───────────────────────────────────────────┐  │
│  │            Main Content Card               │  │
│  │  ┌─────────────────────────────────────┐  │  │
│  │  │        Animated Icon Container       │  │  │
│  │  │      (Settings/Gear Animation)      │  │  │
│  │  └─────────────────────────────────────┘  │  │
│  │  ┌─────────────────────────────────────┐  │  │
│  │  │         MAINTENANCE MODE             │  │  │
│  │  │           Big Heading                │  │  │
│  │  └─────────────────────────────────────┘  │  │
│  │  ┌─────────────────────────────────────┐  │  │
│  │  │    We're currently performing        │  │  │
│  │  │    scheduled maintenance.             │  │  │
│  │  │    We'll be back shortly.            │  │  │
│  │  └─────────────────────────────────────┘  │  │
│  │  ┌─────────────────────────────────────┐  │  │
│  │  │      Subscribe / Refresh Button      │  │  │
│  │  └─────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────┘  │
│                                                  │
│  ┌───────────────────────────────────────────┐  │
│  │              Footer Section                │  │
│  │      UG IRB Portal - Ethics Committee     │  │
│  └───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
```

### Component Specifications

#### 1. Header Section
- **Height**: 120-140px
- **Background**: Dark gradient (`#042c5c` to `#2d3748`)
- **Bottom Border**: 4px solid `#b29762` (Gold)
- **Logo Size**: 60-80px height
- **Text Color**: White

#### 2. Main Content Card
- **Width**: 500-600px (max-width), 90% on mobile
- **Padding**: 60px 40px desktop, 40px 25px mobile
- **Background**: White
- **Border Radius**: 16px
- **Box Shadow**: 0 10px 40px rgba(0, 0, 0, 0.1)
- **Animation**: FadeIn on load (0.5s ease-out)

#### 3. Animated Icon Container
- **Size**: 100-120px circle
- **Icon**: Font Awesome `fa-cog` or `fa-tools`
- **Animation**: Continuous rotation or pulsing
- **Color**: Primary blue gradient

#### 4. Heading Typography
- **Main Heading**: "MAINTENANCE MODE" or "UNDER MAINTENANCE"
- **Font Size**: 36-48px
- **Color**: Primary blue (`#042c5c`)
- **Font Weight**: 700-800
- **Letter Spacing**: 2-4px

#### 5. Body Text
- **Font Size**: 16-18px
- **Color**: Secondary text (`#6c757d`)
- **Line Height**: 1.6-1.8
- **Max Width**: 400px centered

#### 6. Action Button
- **Style**: Primary gradient button
- **Padding**: 14px 28px
- **Border Radius**: 10px
- **Font Weight**: 600
- **Text**: "Check Status" or "Refresh Page"
- **Icon**: `fa-sync-alt` (refresh icon)
- **Hover Effect**: Lift 2px, increase shadow

---

## 3. Animation Strategy

### CSS Animations (No JavaScript Dependencies)

#### 3.1 FadeIn Animation (Page Load)
```css
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

**Usage**: Applied to the main content card on page load. Duration: 0.5s ease-out.

#### 3.2 Rotate Animation (Icon)
```css
@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
```

**Usage**: Applied to the settings/tools icon. Duration: 3s linear infinite.

#### 3.3 Pulse Animation (Icon Glow)
```css
@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(36, 63, 129, 0.4);
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 0 20px 10px rgba(36, 63, 129, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(36, 63, 129, 0);
    }
}
```

**Usage**: Optional subtle pulse effect on the main icon. Duration: 2s infinite.

#### 3.4 Float Animation (Button Hover)
```css
@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}
```

**Usage**: Applied to button on hover. Duration: 0.3s ease-in-out.

### Animation Timing

| Element | Animation | Duration | Delay | Easing |
|---------|-----------|----------|-------|--------|
| Main Card | fadeIn | 0.5s | 0s | ease-out |
| Icon Container | rotate | 3s | 0s | linear |
| Icon Pulse | pulse | 2s | 0s | ease-in-out |
| Heading | fadeIn | 0.5s | 0.2s | ease-out |
| Body Text | fadeIn | 0.5s | 0.4s | ease-out |
| Button | float | 0.3s | 0s | ease-in-out |

---

## 4. Responsive Design

### Breakpoints

| Breakpoint | Max Width | Padding | Font Sizes |
|------------|-----------|---------|------------|
| **Desktop** | >992px | 60px 40px | Normal (as specified) |
| **Tablet** | ≤992px | 50px 35px | Slight reduction |
| **Mobile** | ≤576px | 40px 25px | 20-30% reduction |

### Mobile Optimizations

- Stack logo and text vertically on very small screens
- Reduce icon size from 120px to 80px
- Button takes full width on mobile
- Increase touch target sizes for buttons
- Maintain minimum 16px font for readability

---

## 5. Technical Implementation

### File Structure

```
admin/
├── assets/
│   ├── css/
│   │   └── maintenance.css    (New - dedicated styles)
│   └── images/
│       ├── ug_logo_white.png
│       └── ug_logo.png
└── pages/
    └── maintenance.php        (New - maintenance page)
```

### External Dependencies

| Dependency | Version | Usage |
|------------|---------|-------|
| Bootstrap CSS | 5.3.0 | Grid, utilities, buttons |
| Font Awesome | 6.4.0 | Icons (cog, sync-alt) |

**Note**: No JavaScript required for core animations. Pure CSS for optimal performance.

### Recommended CSS Structure

```css
/* Maintenance Page Specific Styles */

/* Page Container */
.maintenance-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fb 0%, #e8ecf4 100%);
}

/* Card Styles */
.maintenance-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 60px 40px;
    text-align: center;
    max-width: 550px;
    animation: fadeIn 0.5s ease-out;
}

/* Icon Animation */
.maintenance-icon {
    font-size: 100px;
    color: var(--royal-blue);
    margin-bottom: 30px;
    animation: rotate 3s linear infinite;
}

/* Typography */
.maintenance-title {
    font-size: 36px;
    font-weight: 800;
    color: var(--royal-blue);
    margin-bottom: 20px;
    letter-spacing: 2px;
}

.maintenance-message {
    font-size: 18px;
    color: var(--secondary-color);
    line-height: 1.7;
    margin-bottom: 40px;
}

/* Button Styles */
.maintenance-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.maintenance-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(36, 63, 129, 0.4);
    color: white;
}

/* Footer */
.maintenance-footer {
    margin-top: 40px;
    font-size: 14px;
    color: var(--secondary-color);
}

/* Responsive */
@media (max-width: 576px) {
    .maintenance-card {
        margin: 20px;
        padding: 40px 25px;
    }
    
    .maintenance-icon {
        font-size: 80px;
    }
    
    .maintenance-title {
        font-size: 28px;
    }
    
    .maintenance-message {
        font-size: 16px;
    }
}
```

---

## 6. Visual Hierarchy

```
1. Primary Attention: Animated Icon (rotating gear)
   └─ Draws attention, communicates "work in progress"

2. Secondary Attention: "MAINTENANCE MODE" Heading
   └─ Clear status indicator

3. Tertiary Attention: Explanatory Message
   └─ Provides context and reassurance

4. Action: Refresh Button
   └─ Gives users something to do

5. Brand: Logo and Portal Name
   └─ Maintains trust and recognition

6. Footer: Contact/Support Info (optional)
   └─ Provides fallback communication channel
```

---

## 7. Accessibility Considerations

| Feature | Implementation |
|---------|---------------|
| **Color Contrast** | Minimum 4.5:1 ratio for text |
| **Focus States** | Visible focus outline on buttons |
| **Screen Readers** | Proper heading hierarchy (h1 → h2) |
| **Reduced Motion** | Respect `prefers-reduced-motion` media query |
| **Keyboard Navigation** | All interactive elements accessible |
| **Alt Text** | Descriptive alt text for logo images |

```css
@media (prefers-reduced-motion: reduce) {
    .maintenance-icon {
        animation: none;
    }
    
    .maintenance-card {
        animation: none;
    }
}
```

---

## 8. Implementation Checklist

### CSS Tasks
- [ ] Create `admin/assets/css/maintenance.css`
- [ ] Define CSS custom properties for colors
- [ ] Implement fadeIn animation
- [ ] Implement rotate animation
- [ ] Implement pulse animation (optional)
- [ ] Style main content card
- [ ] Style typography
- [ ] Style action button
- [ ] Add responsive breakpoints
- [ ] Add reduced motion support

### HTML Tasks
- [ ] Create `admin/pages/maintenance.php`
- [ ] Include Bootstrap CSS
- [ ] Include Font Awesome
- [ ] Include maintenance.css
- [ ] Add logo and branding
- [ ] Add animated icon
- [ ] Add maintenance heading
- [ ] Add message text
- [ ] Add refresh button
- [ ] Add footer information

### Testing Tasks
- [ ] Test on desktop browsers (Chrome, Firefox, Safari, Edge)
- [ ] Test on mobile devices (iOS, Android)
- [ ] Test responsiveness (all breakpoints)
- [ ] Test reduced motion preference
- [ ] Verify accessibility (contrast, keyboard navigation)
- [ ] Test button hover states
- [ ] Test animation smoothness

---

## 9. Sample Message Copy

### Option A: General Maintenance
```
MAINTENANCE MODE

We're currently performing scheduled maintenance 
to improve our services.

We'll be back shortly. Thank you for your patience.

[Refresh Page]
```

### Option B: Extended Maintenance
```
SYSTEM MAINTENANCE

Our portal is currently undergoing scheduled maintenance.

We expect to be back online within the next hour.

Thank you for your patience.

[Refresh Page]
```

---

## 10. Related Files

- [`admin/assets/css/style.css`](admin/assets/css/style.css) - Main stylesheet with color variables
- [`admin/assets/css/admin-commons.css`](admin/assets/css/admin-commons.css) - Design system tokens
- [`admin/assets/css/header.css`](admin/assets/css/header.css) - Header/navbar styles
- [`admin/404.php`](admin/404.php) - Reference error page with similar structure
- [`admin/includes/header.php`](admin/includes/header.php) - Header include file

---

*Document Version: 1.0*
*Created for: UG IRB Portal*
*Last Updated: 2026-02-12*
