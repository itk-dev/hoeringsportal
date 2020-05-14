/**
 * @file
 * Modify project timeline backend form.
 */

// Move help text of timeline paragraph.
let helpText = document.getElementById('edit-field-timeline-items--description')
let timelineWrapper = document.getElementById('edit-field-project-start-wrapper')
let helpTextWrapper = document.createElement('p')
timelineWrapper.prepend(helpTextWrapper)
helpTextWrapper.appendChild(helpText)
