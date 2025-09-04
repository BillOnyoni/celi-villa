// assets/js/script.js
document.addEventListener('click', function(e){
  if (e.target && e.target.matches('.toggle-dark')) {
    fetch('/settings.php?toggle_dark=1').then(()=> location.reload());
  }
});
