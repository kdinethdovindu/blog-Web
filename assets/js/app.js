(function(){
  var here = location.pathname.replace(/\/+$/,'');
  document.querySelectorAll('.nav a').forEach(a=>{
    var href = a.getAttribute('href');
    try {
      var path = new URL(href, location.origin).pathname.replace(/\/+$/,'');
      if (path === here) a.classList.add('active');
    } catch(e){}
  });
})();
