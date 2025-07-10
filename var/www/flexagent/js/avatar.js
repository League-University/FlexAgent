/**
 * FlexAgent Avatar Animation System
 *
 * This system provides a CSS-based "Clippy-like" avatar with:
 * - Phoneme-based mouth animations for realistic lip sync
 * - Emotional state animations (anger, confusion, heart eyes, etc.)
 * - Natural blinking and eye movement
 * - Text-to-speech-like visual feedback
 *
 * Usage:
 *   const avatar = new Avatar();
 *   avatar.speak("Hello world! 😊");
 */
// Configuration constants
const defaultMouthPosition = "ee"; // Default mouth shape for neutral position
const characterDelay = 100; // 100ms - Base delay between characters
const punctuationPause = 250; // 250ms - Pause for punctuation
const emotionalDuration = 2000; // 2s - How long emotional states last
/**
 * Known multi-character digraphs that should be processed as single units
 * Order matters - longer digraphs should be checked first
 */
const digraphs = ["th", "sh", "ch", "qu", "oo", "ee", "ng", "wh", "ph", "gh"];
/**
 * Avatar Class - Main controller for the FlexAgent avatar animations
 *
 * Manages mouth movements, eye animations, emotional states, and text output.
 * Provides text-to-speech-like visual feedback with phoneme-based lip sync.
 *
 * Key methods:
 * - speak(text): Animate avatar speaking the given text
 * - stop(): Stop current animation and reset to neutral
 * - applyEmotionalState(emotion): Apply emotional animation
 * - clearEmotionalState(): Remove emotional state and return to neutral
 */
class Avatar {
	constructor() {
		this.avatar = document.querySelector(".avatar");
		this.face = document.querySelector(".face");
		this.mouth = document.querySelector(".mouth");
		this.eyes = document.querySelector(".eyes");
		this.output = document.querySelector(".output");
		this.isActive = false;
		this.currentText = "";
		this.blinkInterval = null;
		this.emotionTimeout = null;
		this.currentEmotion = null;
		this.powerOn();
	}

	// CRT power-on sequence
	powerOn() {
		console.log("powerOn() called, face element:", this.face);
		console.log("Adding startup class...");
		this.face.classList.add("startup");
		console.log("Face classes after adding startup:", this.face.className);
		setTimeout(() => {
			console.log("Removing startup class...");
			this.face.classList.remove("startup");
			this.startBlinking();
		}, 1500);
	}
	// Start the speaking animations
	async speak(text) {
		console.log("speak() called with text:", text);
		if (this.isActive) {
			this.stop();
			await this.pause(500);
		}
		this.isActive = true;
		this.currentText = "";
		this.output.textContent = "";
		console.log("About to tokenize text...");
		const tokens = this.tokenize(text);
		console.log("Tokenized text:", tokens);
		for (const { char, token, emotion, pause } of tokens) {
			if (this.isActive == false) break;
			// Add character to output
			this.currentText += char;
			this.output.textContent = this.currentText;
			// Update mouth shape
			if (typeof token != "undefined" && token != null) {
				this.face.setAttribute("data-token", token);
			}
			// Update emotion
			if (typeof emotion != "undefined" && emotion != null) {
				console.log("Setting emotion:", emotion, "on element:", this.face);
				this.clearEmotionalState(); // Clear any existing emotion
				this.face.setAttribute("data-emotion", emotion);
				// Set CSS variable for fallback symbol display
				this.face.style.setProperty("--emotion-symbol", `"${emotion}"`);
				this.currentEmotion = emotion;
				console.log("Element after setting emotion:", this.face.outerHTML);
				// Set timeout to clear emotion after duration
				this.emotionTimeout = setTimeout(() => {
					this.clearEmotionalState();
				}, emotionalDuration);
			}
			// Wait for the specified pause
			await this.pause(pause);
		}
		// Return to neutral position
		this.face.setAttribute("data-token", defaultMouthPosition);
		this.isActive = false;
	}
	// Tokenize text into {char, token, emotion, pause} objects
	tokenize(text) {
		const tokens = [];
		const chars = Array.from(text);
		const lowerChars = Array.from(text.toLowerCase());

		let i = 0;
		let currentWordStart = null;
		let currentWordEnd = null;
		
		// Pre-process to find word boundaries
		const wordBoundaries = [];
		for (let j = 0; j < chars.length; j++) {
			if (/[a-zA-Z]/.test(chars[j])) {
				if (currentWordStart === null) currentWordStart = j;
				currentWordEnd = j;
			} else {
				if (currentWordStart !== null) {
					wordBoundaries.push({ start: currentWordStart, end: currentWordEnd });
					currentWordStart = null;
					currentWordEnd = null;
				}
			}
		}
		// Handle final word
		if (currentWordStart !== null) {
			wordBoundaries.push({ start: currentWordStart, end: currentWordEnd });
		}

		while (i < chars.length) {
			// Check for digraphs first (only for letter characters)
			if (
				i < chars.length - 1 &&
				/[a-z]/i.test(chars[i]) &&
				/[a-z]/i.test(chars[i + 1])
			) {
				const digraph = lowerChars[i] + lowerChars[i + 1];
				if (digraphs.includes(digraph)) {
					// Determine word position for digraphs
					let position = 'middle';
					const wordBoundary = wordBoundaries.find(w => i >= w.start && i <= w.end);
					if (wordBoundary) {
						if (i === wordBoundary.start) position = 'first';
						else if (i + 1 === wordBoundary.end) position = 'last';
					}
					
					tokens.push({
						char: chars[i] + chars[i + 1],
						token: digraph,
						emotion: null,
						pause: this.getPauseForChar(chars[i], position)
					});
					i += 2;
					continue;
				}
			}

			const char = chars[i];

			// Check for emoji
			if (this.isEmoji(char)) {
				console.log(
					"Found emoji:",
					char,
					"code:",
					char.codePointAt(0).toString(16)
				);
				tokens.push({
					char: char,
					token: null,
					emotion: char,
					pause: punctuationPause
				});
				i++;
				continue;
			}

			// Regular character
			const lowerChar = lowerChars[i];
			const token = lowerChar.match(/[a-z]/) ? lowerChar : defaultMouthPosition;
			
			// Determine word position
			let position = 'middle';
			const wordBoundary = wordBoundaries.find(w => i >= w.start && i <= w.end);
			if (wordBoundary) {
				if (i === wordBoundary.start) position = 'first';
				else if (i === wordBoundary.end) position = 'last';
			}
			
			tokens.push({
				char: char,
				token: token,
				emotion: null,
				pause: this.getPauseForChar(char, position)
			});
			i++;
		}

		return tokens;
	}
	// Helper method to detect emoji characters and symbols
	// Broad detection - anything in emoji/symbol land is welcome!
	isEmoji(char) {
		const code = char.codePointAt(0);
		return (
			// Main emoji blocks - comprehensive coverage
			(code >= 0x1f000 && code <= 0x1ffff) || // All emoji ranges
			(code >= 0x2000 && code <= 0x2fff) || // General symbols & punctuation
			(code >= 0xfe00 && code <= 0xfeff) || // Variation selectors & presentation forms
			(code >= 0x200d && code <= 0x200d) || // Zero-width joiner
			// Extended ranges for future emoji
			(code >= 0x3000 && code <= 0x303f) || // CJK symbols
			(code >= 0xe000 && code <= 0xf8ff) // Private use area (custom symbols)
		);
	}
	// Determine pause duration based on natural speech patterns
	getPauseForChar(char, position, word) {
		const lowerChar = char.toLowerCase();
		let baseTiming = characterDelay;
		
		// 1. Letter Category Timing (base timing)
		if (/[aeiouy]/.test(lowerChar)) {
			baseTiming = 250; // Vowels - the "money shots"
		} else if (/[lr]/.test(lowerChar)) {
			baseTiming = 180; // Liquids - they flow
		} else if (/[mn]/.test(lowerChar) || lowerChar === 'ng') {
			baseTiming = 150; // Nasals - they hum
		} else if (/[fvszh]/.test(lowerChar) || lowerChar === 'th' || lowerChar === 'sh' || lowerChar === 'wh' || lowerChar === 'ph' || lowerChar === 'gh') {
			baseTiming = 120; // Fricatives - they sizzle
		} else if (/[pbtdkg]/.test(lowerChar) || lowerChar === 'ch') {
			baseTiming = 80; // Stops - quick pops
		} else {
			baseTiming = 100; // Everything else
		}
		
		// 2. Visual Distinctiveness (big mouth shapes hold longer)
		if (/[owu]/.test(lowerChar)) {
			baseTiming += 50; // Big round shapes
		} else if (/[td]/.test(lowerChar)) {
			baseTiming -= 20; // Subtle shapes
		}
		
		// 3. Word Position Rules
		if (position === 'first') {
			baseTiming += 50; // First letter emphasis
		} else if (position === 'last') {
			baseTiming += 30; // Natural trailing
		}
		
		// 4. Punctuation Influence
		if (/[.!?]/.test(char)) return baseTiming * 3;
		if (/[,;:\n]+/.test(char)) return baseTiming * 2;
		if (char === " ") return baseTiming * 0.4;
		
		return baseTiming;
	}
	// Stop animations
	stop() {
		this.isActive = false;
		this.clearEmotionalState();
		this.currentText = "";
		this.output.textContent = "";
		this.face.setAttribute("data-token", defaultMouthPosition);
		this.face.removeAttribute("data-emotion");
		return new Promise((resolve) => setTimeout(resolve, 200));
	}
	// Utility function for delays
	pause(ms) {
		return new Promise((resolve) => setTimeout(resolve, ms));
	}
	// Start blinking
	startBlinking() {
		this.blinkInterval = setInterval(() => {
			if (Math.random() < 0.3) {
				// 30% chance per interval
				this.eyes.classList.add("closed");
				setTimeout(() => {
					this.eyes.classList.remove("closed");
				}, 150 + Math.random() * 200);
			}
		}, 800 + Math.random() * 1200);
	}
	// Stop blinking
	stopBlinking() {
		if (this.blinkInterval) {
			clearInterval(this.blinkInterval);
			this.blinkInterval = null;
		}
		this.eyes.classList.remove("closed");
	}
	// Clear current emotional state
	clearEmotionalState() {
		// Clear previous timeouts
		if (this.emotionTimeout) {
			clearTimeout(this.emotionTimeout);
		}
		this.emotionTimeout = null;
		// Clear emotion data attribute and CSS variable
		this.face.removeAttribute("data-emotion");
		this.face.style.removeProperty("--emotion-symbol");
		this.currentEmotion = null;
	}
}
