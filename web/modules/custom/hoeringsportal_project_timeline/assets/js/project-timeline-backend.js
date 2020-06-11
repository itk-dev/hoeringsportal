/**
 * @file
 * Modify project timeline backend form.
 */

// Move help text of timeline paragraph.
let helpText = document.getElementById('edit-field-timeline-items--description')
let timelineWrapper = document.getElementById('edit-field-timeline-items-wrapper')
let helpTextWrapper = document.createElement('div')
timelineWrapper.insertBefore(helpTextWrapper, timelineWrapper.firstChild)
helpTextWrapper.appendChild(helpText)
