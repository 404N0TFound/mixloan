(function () {
  'use strict';

  let tabBar1 = document.getElementById('tabBar1');
  let tabPop = document.getElementById('tabPop');
  let shade = document.getElementById('shade');
  let popCancel = document.getElementById('popCancel');

  function popupFunc() {
    tabPop.classList.remove('NotShow');
    shade.classList.remove('NotShow');
  }

  function cancelPop() {
    tabPop.classList.add('NotShow');
    shade.classList.add('NotShow');
  }

  tabBar1.addEventListener('click', popupFunc, false);
  popCancel.addEventListener('click', cancelPop, false);


  // 选项卡
  
})();