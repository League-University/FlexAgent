# Claude's Avatar Development Notes

## Quick Context
- This is just the avatar component of FlexAgent, not the whole system
- User built this sophisticated phoneme-based animation system
- I'm only working on animation improvements, not the broader FlexAgent architecture

## Code Architecture Summary (for Claude)

### Key Files & Locations
- **CSS**: `/var/www/flexagent/css/flexy.css` - All visual styling and animations
- **JS**: `/var/www/flexagent/js/avatar.js` - Animation controller and logic
- **HTML**: `/var/www/flexagent/dashboard.php` - DOM structure (lines 6-35)

### Critical Implementation Details

#### CSS Selector Pattern
```css
[data-letters="phoneme"] {
  @layer mouth { &.mouth { /* mouth shape */ } }
  @layer tongue { .tongue { /* tongue position */ } }
  @layer teeth { .teeth { /* teeth visibility */ } }
}
```
- JavaScript sets `data-letters` attribute on `.mouth` element
- CSS layers ensure proper z-index (mouth < tongue < teeth)
- All shapes use same DOM structure, just different positioning

#### Animation Trigger Pattern
```javascript
// Mouth shape change
this.mouth.setAttribute("data-letters", phoneme);

// Emotional state
this.terminal.classList.add(emotionClass);
```

#### Timing System Logic
- `characterDelay` (100ms): Base per-character timing
- `punctuationPause` (250ms): Pause for commas, colons
- `punctuationPause * 2` (500ms): Pause for periods, exclamation
- Vowels get 1.25x timing, spaces get 0.5x timing

## Key Gotchas When Modifying

### CSS Layers Are Critical
- Must maintain `@layer mouth, tongue, teeth` order
- Don't use regular z-index, it breaks the system
- All mouth shapes must use same layer structure

### Phoneme Mapping Order Matters
```javascript
// In parseText() - digraphs checked FIRST
if (i < text.length - 1) {
  const digraph = lowerText.substring(i, i + 2);
  if (phonemeMap[digraph]) { /* handle digraph */ }
}
// Then single characters
```

### Emotional State Management
- Only one emotion active at a time
- Auto-cleanup after 2 seconds via `setTimeout`
- Must clear previous timeouts to prevent conflicts

### CSS Variable System
- All colors defined in `:root` for easy theming
- Don't hardcode colors in individual rules
- Variables have alpha values built in

## Common Development Patterns

### Adding New Mouth Shape
1. Add to `phonemeMap` object in JS
2. Create CSS rule with same layer structure
3. Use existing positioning patterns (absolute, inset-* properties)
4. Test with: `avatar.mouth.setAttribute("data-letters", "newshape")`

### Adding New Emotion
1. Add emoji to `emojiMap` object
2. Create `.terminal.newemotion` CSS rules
3. Use transforms for animations (better performance)
4. Test with: `avatar.terminal.classList.add("newemotion")`

### Timing Adjustments
- Modify constants at top of JS file
- Or override `getPauseForChar()` for specific characters
- Remember: higher values = slower speech

## Animation Performance Notes

### What Works Well
- CSS transforms (translate, scale, rotate)
- Opacity changes
- CSS animations with transforms
- CSS layers for z-index management

### What to Avoid
- Direct layout changes (width, height, top, left)
- Multiple simultaneous layout-triggering animations
- Complex box-shadow animations (current glow effect is fine)

## State Management Mental Model

### Avatar Instance State
```javascript
this.isActive        // Speaking animation running
this.currentText     // Text being output to screen
this.currentEmotion  // Active emotional state class
this.emotionalTimeout // Timer for emotion cleanup
```

### DOM State
```javascript
.mouth[data-letters]  // Current phoneme shape
.terminal.{emotion}   // Current emotional state
.eyes.closed         // Blinking state
.output.textContent  // Visible text output
```

## Testing Shortcuts

### Manual Testing Commands
```javascript
// Test mouth shapes
avatar.mouth.setAttribute("data-letters", "bmp");

// Test emotions
avatar.terminal.classList.add("angry");

// Test speech
avatar.speak("Hello world! 😊");

// Stop everything
avatar.stop();
```

### Common Debug Checks
- Check if `data-letters` attribute is being set
- Verify CSS class additions to `.terminal`
- Confirm setTimeout cleanup is working
- Watch for console errors in parseText()

## Integration Points

### Where Avatar Connects to FlexAgent
- Initialized in `dashboard.php` lines 43-46
- Attached to `window.avatar` for global access
- Click handler on terminal for demo (line 52-54)
- No other FlexAgent integration visible in current scope

### What I Should NOT Touch
- PHP routing system
- Database components (SQL.php, etc.)
- Authentication/session management
- Other FlexAgent modules outside avatar scope

## Current Implementation Quality

### Strengths
- Very sophisticated phoneme mapping
- Smooth CSS animations
- Proper state management
- Good separation of concerns
- Comprehensive emoji support

### Areas for Potential Improvement
- Could add more nuanced timing based on phoneme complexity
- Eye movement could be more dynamic
- Could add micro-expressions between phonemes
- Animation easing could be more varied

## My Role Context
- Focus only on animation improvements
- Don't restructure the broader FlexAgent architecture
- User knows the system well, built it themselves
- Provide technical implementation suggestions, not explanations of what exists