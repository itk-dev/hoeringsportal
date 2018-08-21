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
        console.log($(this).offset().top);
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgMzA5ODYyNDUwNDZiZmVhYzJlNGQiLCJ3ZWJwYWNrOi8vLy4vanMvaG9lcmluZ3Nwb3J0YWxfcXVpY2tsaW5rcy5qcyIsIndlYnBhY2s6Ly8vLi9qcy9wcm9qZWN0LXF1aWNrbGlua3MuanMiXSwibmFtZXMiOlsiJCIsIkRydXBhbCIsImJlaGF2aW9ycyIsInF1aWNrTGlua3MiLCJhdHRhY2giLCJjb250ZXh0Iiwic2V0dGluZ3MiLCJlYWNoIiwiaW5kZXgiLCJ2YWx1ZSIsImFwcGVuZCIsImh0bWwiLCJhdHRyIiwiZG9jdW1lbnQiLCJvbiIsImV2ZW50IiwiY29uc29sZSIsImxvZyIsIm9mZnNldCIsInRvcCIsInByZXZlbnREZWZhdWx0IiwiYW5pbWF0ZSIsInNjcm9sbFRvcCIsImpRdWVyeSIsInJlcXVpcmUiXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7O0FBR0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBSztBQUNMO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsbUNBQTJCLDBCQUEwQixFQUFFO0FBQ3ZELHlDQUFpQyxlQUFlO0FBQ2hEO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLDhEQUFzRCwrREFBK0Q7O0FBRXJIO0FBQ0E7O0FBRUE7QUFDQTs7Ozs7Ozs7Ozs7OztBQzdEQTs7Ozs7QUFLQTtBQUNBLENBQUMsVUFBVUEsQ0FBVixFQUFhQyxNQUFiLEVBQXFCO0FBQ3BCQSxTQUFPQyxTQUFQLENBQWlCQyxVQUFqQixHQUE4QjtBQUM1QkMsWUFBUSxnQkFBVUMsT0FBVixFQUFtQkMsUUFBbkIsRUFBNkI7QUFDbkM7O0FBRUE7O0FBQ0FOLFFBQUVPLElBQUYsQ0FBT1AsRUFBRSxxQ0FBRixDQUFQLEVBQWlELFVBQVVRLEtBQVYsRUFBaUJDLEtBQWpCLEVBQXdCO0FBQ3ZFO0FBQ0FULFVBQUUsYUFBRixFQUFpQlUsTUFBakIsQ0FBd0IsMkJBQTJCRixLQUEzQixHQUFtQyxJQUFuQyxHQUEwQ1IsRUFBRSxJQUFGLEVBQVFXLElBQVIsRUFBMUMsR0FBMkQsWUFBbkY7QUFDQTtBQUNBWCxVQUFFLElBQUYsRUFBUVksSUFBUixDQUFhLElBQWIsRUFBbUIsYUFBYUosS0FBaEM7QUFDRCxPQUxEOztBQU9BO0FBQ0FSLFFBQUVhLFFBQUYsRUFBWUMsRUFBWixDQUFlLE9BQWYsRUFBd0IsZUFBeEIsRUFBeUMsVUFBVUMsS0FBVixFQUFpQjtBQUN4REMsZ0JBQVFDLEdBQVIsQ0FBWWpCLEVBQUUsSUFBRixFQUFRa0IsTUFBUixHQUFpQkMsR0FBN0I7QUFDQUosY0FBTUssY0FBTjtBQUNBcEIsVUFBRSxZQUFGLEVBQWdCcUIsT0FBaEIsQ0FBd0I7QUFDdEJDLHFCQUFXdEIsRUFBRSxJQUFGLEVBQVFrQixNQUFSLEdBQWlCQztBQUROLFNBQXhCLEVBRUcsR0FGSDtBQUdELE9BTkQ7QUFPRDtBQXBCMkIsR0FBOUI7QUFzQkQsQ0F2QkQsRUF1QkdJLE1BdkJILEVBdUJXdEIsTUF2QlgsRTs7Ozs7Ozs7Ozs7O0FDTkEsbUJBQUF1QixDQUFRLHlFQUFSLEUiLCJmaWxlIjoicHJvamVjdC1xdWlja2xpbmtzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiIFx0Ly8gVGhlIG1vZHVsZSBjYWNoZVxuIFx0dmFyIGluc3RhbGxlZE1vZHVsZXMgPSB7fTtcblxuIFx0Ly8gVGhlIHJlcXVpcmUgZnVuY3Rpb25cbiBcdGZ1bmN0aW9uIF9fd2VicGFja19yZXF1aXJlX18obW9kdWxlSWQpIHtcblxuIFx0XHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcbiBcdFx0aWYoaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0pIHtcbiBcdFx0XHRyZXR1cm4gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0uZXhwb3J0cztcbiBcdFx0fVxuIFx0XHQvLyBDcmVhdGUgYSBuZXcgbW9kdWxlIChhbmQgcHV0IGl0IGludG8gdGhlIGNhY2hlKVxuIFx0XHR2YXIgbW9kdWxlID0gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0gPSB7XG4gXHRcdFx0aTogbW9kdWxlSWQsXG4gXHRcdFx0bDogZmFsc2UsXG4gXHRcdFx0ZXhwb3J0czoge31cbiBcdFx0fTtcblxuIFx0XHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cbiBcdFx0bW9kdWxlc1ttb2R1bGVJZF0uY2FsbChtb2R1bGUuZXhwb3J0cywgbW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cbiBcdFx0Ly8gRmxhZyB0aGUgbW9kdWxlIGFzIGxvYWRlZFxuIFx0XHRtb2R1bGUubCA9IHRydWU7XG5cbiBcdFx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcbiBcdFx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xuIFx0fVxuXG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlcyBvYmplY3QgKF9fd2VicGFja19tb2R1bGVzX18pXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm0gPSBtb2R1bGVzO1xuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZSBjYWNoZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5jID0gaW5zdGFsbGVkTW9kdWxlcztcblxuIFx0Ly8gZGVmaW5lIGdldHRlciBmdW5jdGlvbiBmb3IgaGFybW9ueSBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSBmdW5jdGlvbihleHBvcnRzLCBuYW1lLCBnZXR0ZXIpIHtcbiBcdFx0aWYoIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBuYW1lKSkge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBuYW1lLCB7XG4gXHRcdFx0XHRjb25maWd1cmFibGU6IGZhbHNlLFxuIFx0XHRcdFx0ZW51bWVyYWJsZTogdHJ1ZSxcbiBcdFx0XHRcdGdldDogZ2V0dGVyXG4gXHRcdFx0fSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiL2J1aWxkL1wiO1xuXG4gXHQvLyBMb2FkIGVudHJ5IG1vZHVsZSBhbmQgcmV0dXJuIGV4cG9ydHNcbiBcdHJldHVybiBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IFwiLi9qcy9wcm9qZWN0LXF1aWNrbGlua3MuanNcIik7XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gd2VicGFjay9ib290c3RyYXAgMzA5ODYyNDUwNDZiZmVhYzJlNGQiLCIvKipcbiAqIEBmaWxlXG4gKiBBZGQgcXVpY2tsaW5rcy5cbiAqL1xuXG4vKiBnbG9iYWwgalF1ZXJ5LCBEcnVwYWwgKi9cbihmdW5jdGlvbiAoJCwgRHJ1cGFsKSB7XG4gIERydXBhbC5iZWhhdmlvcnMucXVpY2tMaW5rcyA9IHtcbiAgICBhdHRhY2g6IGZ1bmN0aW9uIChjb250ZXh0LCBzZXR0aW5ncykge1xuICAgICAgJ3VzZSBzdHJpY3QnXG5cbiAgICAgIC8vIENyZWF0ZSBhbmNob3JzIGFuZCBhbmNob3IgbGlua3MuXG4gICAgICAkLmVhY2goJCgnLmNvbnRlbnRfX21haW4gaDIsIC5ncm91cC1mb290ZXIgaDInKSwgZnVuY3Rpb24gKGluZGV4LCB2YWx1ZSkge1xuICAgICAgICAvLyBBcHBlbmQgdGhlIHRleHQgb2YgeW91ciBoZWFkZXIgdG8gYSBsaXN0IGl0ZW0gaW4gYSBkaXYsIGxpbmtpbmcgdG8gYW4gYW5jaG9yIHdlIHdpbGwgY3JlYXRlIG9uIHRoZSBuZXh0IGxpbmVcbiAgICAgICAgJCgnI3F1aWNrbGlua3MnKS5hcHBlbmQoJzxkaXY+PGEgaHJlZj1cIiNhbmNob3ItJyArIGluZGV4ICsgJ1wiPicgKyAkKHRoaXMpLmh0bWwoKSArICc8L2E+PC9kaXY+JylcbiAgICAgICAgLy8gQWRkIGFuIGEgdGFnIHRvIHRoZSBoZWFkZXIgd2l0aCBhIHNlcXVlbnRpYWwgbmFtZVxuICAgICAgICAkKHRoaXMpLmF0dHIoJ2lkJywgJyNhbmNob3ItJyArIGluZGV4KVxuICAgICAgfSlcblxuICAgICAgLy8gQWRkIHNtb290aCBzY3JvbGxpbmcuXG4gICAgICAkKGRvY3VtZW50KS5vbignY2xpY2snLCAnI3F1aWNrbGlua3MgYScsIGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICBjb25zb2xlLmxvZygkKHRoaXMpLm9mZnNldCgpLnRvcCk7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KClcbiAgICAgICAgJCgnaHRtbCwgYm9keScpLmFuaW1hdGUoe1xuICAgICAgICAgIHNjcm9sbFRvcDogJCh0aGlzKS5vZmZzZXQoKS50b3BcbiAgICAgICAgfSwgNTAwKVxuICAgICAgfSlcbiAgICB9XG4gIH1cbn0pKGpRdWVyeSwgRHJ1cGFsKVxuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vanMvaG9lcmluZ3Nwb3J0YWxfcXVpY2tsaW5rcy5qcyIsInJlcXVpcmUoJy4vaG9lcmluZ3Nwb3J0YWxfcXVpY2tsaW5rcy5qcycpXG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9qcy9wcm9qZWN0LXF1aWNrbGlua3MuanMiXSwic291cmNlUm9vdCI6IiJ9