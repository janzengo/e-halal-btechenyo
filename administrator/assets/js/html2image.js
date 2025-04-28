/**
 * HTML to Image conversion utility for E-Halal BTECHenyo
 * Uses html-to-image library for generating high-quality captures of DOM elements
 */

(function() {
    // First try to get the html-to-image library
    let htmlToImage;
    
    // Try to get the library in different environments
    try {
        // Check if we're in a Node.js environment
        if (typeof require !== 'undefined') {
            htmlToImage = require('html-to-image');
        } 
        // Check if it's already available in the browser
        else if (typeof window.htmlToImage !== 'undefined') {
            htmlToImage = window.htmlToImage;
        } 
        // Last resort - look for it on the window object
        else if (typeof window !== 'undefined') {
            htmlToImage = window.htmlToImage || {};
        }
    } catch (e) {
        console.warn('html-to-image module not found or could not be loaded automatically');
    }

    /**
     * Pre-process element before capture to handle CORS issues
     * @param {HTMLElement} element - The element to process
     * @returns {Promise<void>}
     */
    async function preprocessElement(element) {
        try {
            // Find all images and try to convert them to data URLs
            const images = element.getElementsByTagName('img');
            for (let img of images) {
                try {
                    if (!img.src.startsWith('data:')) {
                        const response = await fetch(img.src);
                        const blob = await response.blob();
                        const reader = new FileReader();
                        await new Promise((resolve, reject) => {
                            reader.onload = () => {
                                img.src = reader.result;
                                resolve();
                            };
                            reader.onerror = reject;
                            reader.readAsDataURL(blob);
                        });
                    }
                } catch (imgError) {
                    console.warn(`Failed to convert image to data URL: ${img.src}`, imgError);
                    // If conversion fails, try to use a placeholder
                    img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
                }
            }

            // Handle background images in computed styles
            const elements = element.getElementsByTagName('*');
            for (let el of elements) {
                const style = window.getComputedStyle(el);
                if (style.backgroundImage && style.backgroundImage !== 'none' && !style.backgroundImage.startsWith('data:')) {
                    try {
                        const url = style.backgroundImage.slice(4, -1).replace(/["']/g, "");
                        const response = await fetch(url);
                        const blob = await response.blob();
                        const reader = new FileReader();
                        await new Promise((resolve, reject) => {
                            reader.onload = () => {
                                el.style.backgroundImage = `url(${reader.result})`;
                                resolve();
                            };
                            reader.onerror = reject;
                            reader.readAsDataURL(blob);
                        });
                    } catch (bgError) {
                        console.warn(`Failed to convert background image: ${style.backgroundImage}`, bgError);
                        el.style.backgroundImage = 'none';
                    }
                }
            }
        } catch (error) {
            console.error('Error during element preprocessing:', error);
        }
    }

    /**
     * Capture an HTML element as a base64 image
     * @param {string|HTMLElement} element - The element ID or DOM element to capture
     * @param {Object} options - Configuration options for the capture
     * @returns {Promise<string>} - A promise that resolves to a base64 data URL
     */
    async function captureElement(element, options = {}) {
        return new Promise(async (resolve, reject) => {
            try {
                console.log('Starting element capture...');
                
                // Get the DOM element
                const targetElement = typeof element === 'string' 
                    ? document.getElementById(element) 
                    : element;
                
                if (!targetElement) {
                    throw new Error('Target element not found');
                }

                // Pre-process element to handle CORS issues
                await preprocessElement(targetElement);

                // Default options - Preset to avoid CORS issues completely
                const defaultOptions = {
                    quality: 0.92,
                    pixelRatio: 2,
                    backgroundColor: '#ffffff',
                    skipFonts: true,
                    skipStylesheets: true,
                    fontEmbedCSS: '',
                    inlineImages: true,
                    cacheBust: true,
                    filter: (node) => {
                        // Filter out problematic elements
                        return !['IFRAME', 'SCRIPT', 'NOSCRIPT'].includes(node.tagName);
                    }
                };

                // Merge options
                const captureOptions = { ...defaultOptions, ...options };
                
                console.log('Capture options:', captureOptions);

                // If we have the html-to-image library
                if (htmlToImage && typeof htmlToImage.toJpeg === 'function') {
                    try {
                        console.log('Using html-to-image JPEG method...');
                        const dataUrl = await htmlToImage.toJpeg(targetElement, captureOptions);
                        console.log('JPEG capture successful!');
                        resolve(dataUrl);
                    } catch (jpegError) {
                        console.warn('JPEG capture failed, trying PNG...', jpegError);
                        try {
                            const pngDataUrl = await htmlToImage.toPng(targetElement, {
                                ...captureOptions,
                                skipFonts: true,
                                skipStylesheets: true
                            });
                            console.log('PNG capture successful, converting to JPEG...');
                            const jpegUrl = await convertPngToJpeg(pngDataUrl);
                            resolve(jpegUrl);
                        } catch (pngError) {
                            console.warn('PNG capture failed, falling back to html2canvas...', pngError);
                            const fallbackUrl = await fallbackToHtml2Canvas(targetElement, captureOptions);
                            resolve(fallbackUrl);
                        }
                    }
                } else {
                    console.log('html-to-image not available, using html2canvas directly');
                    const fallbackUrl = await fallbackToHtml2Canvas(targetElement, captureOptions);
                    resolve(fallbackUrl);
                }
            } catch (error) {
                console.error('Fatal error during capture:', error);
                reject(error);
            }
        });
    }

    /**
     * Convert PNG data URL to JPEG data URL
     * @param {string} pngDataUrl - The PNG data URL to convert
     * @returns {Promise<string>} - A promise that resolves to a JPEG data URL
     */
    async function convertPngToJpeg(pngDataUrl) {
        return new Promise((resolve, reject) => {
            try {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                
                img.onload = () => {
                    try {
                        // Create canvas and get context
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext('2d');
                        
                        // Fill with white background to remove transparency
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        
                        // Draw the image
                        ctx.drawImage(img, 0, 0);
                        
                        // Convert to JPEG
                        const jpegDataUrl = canvas.toDataURL('image/jpeg', 0.92);
                        resolve(jpegDataUrl);
                    } catch (canvasError) {
                        console.error('Error during canvas operations:', canvasError);
                        reject(canvasError);
                    }
                };
                
                img.onerror = (error) => {
                    console.error('Error loading PNG for conversion:', error);
                    reject(error);
                };
                
                img.src = pngDataUrl;
            } catch (error) {
                console.error('Error in PNG to JPEG conversion:', error);
                reject(error);
            }
        });
    }

    /**
     * Fallback to html2canvas when html-to-image fails
     * @param {HTMLElement} element - The element to capture
     * @param {Object} options - Configuration options
     * @returns {Promise<string>} - A promise that resolves to a base64 data URL
     */
    async function fallbackToHtml2Canvas(element, options = {}) {
        return new Promise(async (resolve, reject) => {
            try {
                if (typeof html2canvas === 'undefined') {
                    throw new Error('html2canvas is not available');
                }

                console.log('Using html2canvas fallback...');
                
                // Configure html2canvas options
                const html2canvasOptions = {
                    scale: options.pixelRatio || 2,
                    backgroundColor: options.backgroundColor || '#ffffff',
                    logging: false,
                    removeContainer: true,
                    allowTaint: true,
                    useCORS: true,
                    onclone: (clonedDoc) => {
                        // Additional processing on cloned document if needed
                        try {
                            const clonedElement = clonedDoc.querySelector(`#${element.id}`);
                            if (clonedElement) {
                                // Remove any problematic elements
                                const problematicElements = clonedElement.querySelectorAll('iframe, script, noscript');
                                problematicElements.forEach(el => el.remove());
                            }
                        } catch (cloneError) {
                            console.warn('Error in clone processing:', cloneError);
                        }
                    }
                };

                const canvas = await html2canvas(element, html2canvasOptions);
                const dataUrl = canvas.toDataURL('image/jpeg', options.quality || 0.92);
                console.log('html2canvas capture successful!');
                resolve(dataUrl);
            } catch (error) {
                console.error('html2canvas fallback failed:', error);
                reject(error);
            }
        });
    }

    /**
     * Capture an element and send its data URL to a hidden form field
     * @param {string|HTMLElement} element - The element to capture
     * @param {string} formFieldId - The ID of the form field to populate
     * @param {Object} options - Capture options
     */
    function captureToFormField(element, formFieldId, options = {}) {
        const targetField = document.getElementById(formFieldId);
        if (!targetField) {
            console.error(`Form field with ID ${formFieldId} not found`);
            return;
        }

        console.log(`Starting capture for form field: ${formFieldId}`);
        
        captureElement(element, options)
            .then(dataUrl => {
                targetField.value = dataUrl;
                console.log(`Capture successful, data stored in ${formFieldId} (${dataUrl.substring(0, 50)}...)`);
            })
            .catch(error => {
                console.error('Failed to capture element:', error);
                // Set a placeholder or error value in the form field
                targetField.value = '';
            });
    }

    /**
     * Capture multiple elements and combine them into a single image
     * @param {Array<string|HTMLElement>} elements - Array of element IDs or DOM elements
     * @param {Object} options - Configuration options
     * @returns {Promise<string>} - A promise that resolves to a base64 data URL
     */
    async function captureMultipleElements(elements, options = {}) {
        try {
            const captures = await Promise.all(
                elements.map(element => captureElement(element, options))
            );

            // Create a canvas to combine all captures
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            let totalHeight = 0;
            let maxWidth = 0;

            // Calculate dimensions
            const images = await Promise.all(captures.map(dataUrl => {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.onerror = reject;
                    img.src = dataUrl;
                });
            }));

            images.forEach(img => {
                totalHeight += img.height;
                maxWidth = Math.max(maxWidth, img.width);
            });

            // Set canvas dimensions
            canvas.width = maxWidth;
            canvas.height = totalHeight;

            // Fill with white background
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Draw images
            let currentY = 0;
            images.forEach(img => {
                ctx.drawImage(img, 0, currentY);
                currentY += img.height;
            });

            // Convert to JPEG
            return canvas.toDataURL('image/jpeg', options.quality || 0.92);
        } catch (error) {
            console.error('Error capturing multiple elements:', error);
            throw error;
        }
    }

    // Make functions globally available
    window.htmlToImageTools = {
        captureElement,
        captureToFormField,
        captureMultipleElements,
        convertPngToJpeg,
        preprocessElement
    };
})(); 