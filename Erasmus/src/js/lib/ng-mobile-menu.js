angular.module('shoppinpal.mobile-menu', [])
    .run(['$rootScope', '$spMenu', function($rootScope, $spMenu){
        $rootScope.$spMenu = $spMenu;
    }])
    .provider("$spMenu", function(){
        this.$get = [function(){
           var menu = {};

           menu.show = function show(){
               var menu = angular.element(document.querySelector('#sp-nav'));
               var page = angular.element(document.querySelector('#sp-page'));
               console.log(page);
               menu.addClass('show');
               page.addClass('menu-out');
           };

           menu.hide = function hide(){
               var menu = angular.element(document.querySelector('#sp-nav'));
               var page = angular.element(document.querySelector('#sp-page'));
               menu.removeClass('show');               
               page.removeClass('menu-out');
           };

           menu.toggle = function toggle() {
               var checklists = angular.element(document.querySelectorAll('.questionAnswerInputs'));
               console.log(checklists);
               checklists.toggleClass('dropInputs');

               var menuToggle = angular.element(document.querySelector('.menuIcon'));
               menuToggle.toggleClass('pushLeft');

               var menu = angular.element(document.querySelector('#sp-nav'));
               var page = angular.element(document.querySelector('#sp-page'));
               menu.toggleClass('show');
               page.toggleClass('menu-out');
           };

           return menu;
        }];
    });