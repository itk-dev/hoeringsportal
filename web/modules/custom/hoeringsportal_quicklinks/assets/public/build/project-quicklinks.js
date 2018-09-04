/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/build/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./js/project-quicklinks.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/hoeringsportal_quicklinks.js":
/*!*****************************************!*\
  !*** ./js/hoeringsportal_quicklinks.js ***!
  \*****************************************/
/*! dynamic exports provided */
/*! all exports used */
/***/ (function(module, exports) {

/**
 * @file
 * Add quicklinks.
 */

/* global jQuery, Drupal */
(function ($, Drupal) {
  Drupal.behaviors.quickLinks = {
    attach: function attach(context, settings) {
      'use strict';

      // Create anchors and anchor links.

      $.each($('.content__main h2, .group-footer h2'), function (index, value) {
        // Append the text of your header to a list item in a div, linking to an anchor we will create on the next line
        $('#quicklinks').append('<div><a href="#anchor-' + index + '">' + $(this).html() + '</a></div>');
        // Add an a tag to the header with a sequential name
        $(this).attr('id', '#anchor-' + index);
      });

      // Add smooth scrolling.
      $(document).on('click', '#quicklinks a', function (event) {
        event.preventDefault();
        $('html, body').animate({
          scrollTop: $(this).offset().top
        }, 500);
      });
    }
  };
})(jQuery, Drupal);

/***/ }),

/***/ "./js/project-quicklinks.js":
/*!**********************************!*\
  !*** ./js/project-quicklinks.js ***!
  \**********************************/
/*! dynamic exports provided */
/*! all exports used */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./hoeringsportal_quicklinks.js */ "./js/hoeringsportal_quicklinks.js");

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgNzI1YmUyMTY2MjE5ZDNiY2Y4MDMiLCJ3ZWJwYWNrOi8vLy4vanMvaG9lcmluZ3Nwb3J0YWxfcXVpY2tsaW5rcy5qcyIsIndlYnBhY2s6Ly8vLi9qcy9wcm9qZWN0LXF1aWNrbGlua3MuanMiXSwibmFtZXMiOlsiJCIsIkRydXBhbCIsImJlaGF2aW9ycyIsInF1aWNrTGlua3MiLCJhdHRhY2giLCJjb250ZXh0Iiwic2V0dGluZ3MiLCJlYWNoIiwiaW5kZXgiLCJ2YWx1ZSIsImFwcGVuZCIsImh0bWwiLCJhdHRyIiwiZG9jdW1lbnQiLCJvbiIsImV2ZW50IiwicHJldmVudERlZmF1bHQiLCJhbmltYXRlIiwic2Nyb2xsVG9wIiwib2Zmc2V0IiwidG9wIiwialF1ZXJ5IiwicmVxdWlyZSJdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFLO0FBQ0w7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxtQ0FBMkIsMEJBQTBCLEVBQUU7QUFDdkQseUNBQWlDLGVBQWU7QUFDaEQ7QUFDQTtBQUNBOztBQUVBO0FBQ0EsOERBQXNELCtEQUErRDs7QUFFckg7QUFDQTs7QUFFQTtBQUNBOzs7Ozs7Ozs7Ozs7O0FDN0RBOzs7OztBQUtBO0FBQ0EsQ0FBQyxVQUFVQSxDQUFWLEVBQWFDLE1BQWIsRUFBcUI7QUFDcEJBLFNBQU9DLFNBQVAsQ0FBaUJDLFVBQWpCLEdBQThCO0FBQzVCQyxZQUFRLGdCQUFVQyxPQUFWLEVBQW1CQyxRQUFuQixFQUE2QjtBQUNuQzs7QUFFQTs7QUFDQU4sUUFBRU8sSUFBRixDQUFPUCxFQUFFLHFDQUFGLENBQVAsRUFBaUQsVUFBVVEsS0FBVixFQUFpQkMsS0FBakIsRUFBd0I7QUFDdkU7QUFDQVQsVUFBRSxhQUFGLEVBQWlCVSxNQUFqQixDQUF3QiwyQkFBMkJGLEtBQTNCLEdBQW1DLElBQW5DLEdBQTBDUixFQUFFLElBQUYsRUFBUVcsSUFBUixFQUExQyxHQUEyRCxZQUFuRjtBQUNBO0FBQ0FYLFVBQUUsSUFBRixFQUFRWSxJQUFSLENBQWEsSUFBYixFQUFtQixhQUFhSixLQUFoQztBQUNELE9BTEQ7O0FBT0E7QUFDQVIsUUFBRWEsUUFBRixFQUFZQyxFQUFaLENBQWUsT0FBZixFQUF3QixlQUF4QixFQUF5QyxVQUFVQyxLQUFWLEVBQWlCO0FBQ3hEQSxjQUFNQyxjQUFOO0FBQ0FoQixVQUFFLFlBQUYsRUFBZ0JpQixPQUFoQixDQUF3QjtBQUN0QkMscUJBQVdsQixFQUFFLElBQUYsRUFBUW1CLE1BQVIsR0FBaUJDO0FBRE4sU0FBeEIsRUFFRyxHQUZIO0FBR0QsT0FMRDtBQU1EO0FBbkIyQixHQUE5QjtBQXFCRCxDQXRCRCxFQXNCR0MsTUF0QkgsRUFzQldwQixNQXRCWCxFOzs7Ozs7Ozs7Ozs7QUNOQSxtQkFBQXFCLENBQVEseUVBQVIsRSIsImZpbGUiOiJwcm9qZWN0LXF1aWNrbGlua3MuanMiLCJzb3VyY2VzQ29udGVudCI6WyIgXHQvLyBUaGUgbW9kdWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuIFx0ZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXG4gXHRcdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuIFx0XHRpZihpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSkge1xuIFx0XHRcdHJldHVybiBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXS5leHBvcnRzO1xuIFx0XHR9XG4gXHRcdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG4gXHRcdHZhciBtb2R1bGUgPSBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSA9IHtcbiBcdFx0XHRpOiBtb2R1bGVJZCxcbiBcdFx0XHRsOiBmYWxzZSxcbiBcdFx0XHRleHBvcnRzOiB7fVxuIFx0XHR9O1xuXG4gXHRcdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuIFx0XHRtb2R1bGVzW21vZHVsZUlkXS5jYWxsKG1vZHVsZS5leHBvcnRzLCBtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuIFx0XHQvLyBGbGFnIHRoZSBtb2R1bGUgYXMgbG9hZGVkXG4gXHRcdG1vZHVsZS5sID0gdHJ1ZTtcblxuIFx0XHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuIFx0XHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG4gXHR9XG5cblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubSA9IG1vZHVsZXM7XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlIGNhY2hlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmMgPSBpbnN0YWxsZWRNb2R1bGVzO1xuXG4gXHQvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9uIGZvciBoYXJtb255IGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uZCA9IGZ1bmN0aW9uKGV4cG9ydHMsIG5hbWUsIGdldHRlcikge1xuIFx0XHRpZighX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIG5hbWUpKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIG5hbWUsIHtcbiBcdFx0XHRcdGNvbmZpZ3VyYWJsZTogZmFsc2UsXG4gXHRcdFx0XHRlbnVtZXJhYmxlOiB0cnVlLFxuIFx0XHRcdFx0Z2V0OiBnZXR0ZXJcbiBcdFx0XHR9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZ2V0RGVmYXVsdEV4cG9ydCBmdW5jdGlvbiBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIG5vbi1oYXJtb255IG1vZHVsZXNcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubiA9IGZ1bmN0aW9uKG1vZHVsZSkge1xuIFx0XHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cbiBcdFx0XHRmdW5jdGlvbiBnZXREZWZhdWx0KCkgeyByZXR1cm4gbW9kdWxlWydkZWZhdWx0J107IH0gOlxuIFx0XHRcdGZ1bmN0aW9uIGdldE1vZHVsZUV4cG9ydHMoKSB7IHJldHVybiBtb2R1bGU7IH07XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18uZChnZXR0ZXIsICdhJywgZ2V0dGVyKTtcbiBcdFx0cmV0dXJuIGdldHRlcjtcbiBcdH07XG5cbiBcdC8vIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbFxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5vID0gZnVuY3Rpb24ob2JqZWN0LCBwcm9wZXJ0eSkgeyByZXR1cm4gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iamVjdCwgcHJvcGVydHkpOyB9O1xuXG4gXHQvLyBfX3dlYnBhY2tfcHVibGljX3BhdGhfX1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5wID0gXCIvYnVpbGQvXCI7XG5cbiBcdC8vIExvYWQgZW50cnkgbW9kdWxlIGFuZCByZXR1cm4gZXhwb3J0c1xuIFx0cmV0dXJuIF9fd2VicGFja19yZXF1aXJlX18oX193ZWJwYWNrX3JlcXVpcmVfXy5zID0gXCIuL2pzL3Byb2plY3QtcXVpY2tsaW5rcy5qc1wiKTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyB3ZWJwYWNrL2Jvb3RzdHJhcCA3MjViZTIxNjYyMTlkM2JjZjgwMyIsIi8qKlxuICogQGZpbGVcbiAqIEFkZCBxdWlja2xpbmtzLlxuICovXG5cbi8qIGdsb2JhbCBqUXVlcnksIERydXBhbCAqL1xuKGZ1bmN0aW9uICgkLCBEcnVwYWwpIHtcbiAgRHJ1cGFsLmJlaGF2aW9ycy5xdWlja0xpbmtzID0ge1xuICAgIGF0dGFjaDogZnVuY3Rpb24gKGNvbnRleHQsIHNldHRpbmdzKSB7XG4gICAgICAndXNlIHN0cmljdCdcblxuICAgICAgLy8gQ3JlYXRlIGFuY2hvcnMgYW5kIGFuY2hvciBsaW5rcy5cbiAgICAgICQuZWFjaCgkKCcuY29udGVudF9fbWFpbiBoMiwgLmdyb3VwLWZvb3RlciBoMicpLCBmdW5jdGlvbiAoaW5kZXgsIHZhbHVlKSB7XG4gICAgICAgIC8vIEFwcGVuZCB0aGUgdGV4dCBvZiB5b3VyIGhlYWRlciB0byBhIGxpc3QgaXRlbSBpbiBhIGRpdiwgbGlua2luZyB0byBhbiBhbmNob3Igd2Ugd2lsbCBjcmVhdGUgb24gdGhlIG5leHQgbGluZVxuICAgICAgICAkKCcjcXVpY2tsaW5rcycpLmFwcGVuZCgnPGRpdj48YSBocmVmPVwiI2FuY2hvci0nICsgaW5kZXggKyAnXCI+JyArICQodGhpcykuaHRtbCgpICsgJzwvYT48L2Rpdj4nKVxuICAgICAgICAvLyBBZGQgYW4gYSB0YWcgdG8gdGhlIGhlYWRlciB3aXRoIGEgc2VxdWVudGlhbCBuYW1lXG4gICAgICAgICQodGhpcykuYXR0cignaWQnLCAnI2FuY2hvci0nICsgaW5kZXgpXG4gICAgICB9KVxuXG4gICAgICAvLyBBZGQgc21vb3RoIHNjcm9sbGluZy5cbiAgICAgICQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcjcXVpY2tsaW5rcyBhJywgZnVuY3Rpb24gKGV2ZW50KSB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KClcbiAgICAgICAgJCgnaHRtbCwgYm9keScpLmFuaW1hdGUoe1xuICAgICAgICAgIHNjcm9sbFRvcDogJCh0aGlzKS5vZmZzZXQoKS50b3BcbiAgICAgICAgfSwgNTAwKVxuICAgICAgfSlcbiAgICB9XG4gIH1cbn0pKGpRdWVyeSwgRHJ1cGFsKVxuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vanMvaG9lcmluZ3Nwb3J0YWxfcXVpY2tsaW5rcy5qcyIsInJlcXVpcmUoJy4vaG9lcmluZ3Nwb3J0YWxfcXVpY2tsaW5rcy5qcycpXG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9qcy9wcm9qZWN0LXF1aWNrbGlua3MuanMiXSwic291cmNlUm9vdCI6IiJ9