(function(){
  const txt = document.querySelector('[name="content"]');
  const box = document.getElementById('preview-box');
  const toggle = document.getElementById('toggle-preview');
  if(!txt || !box || !toggle) return;

  function esc(s){return s.replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]))}
  function md(s){
    s = esc(s);
    s = s.replace(/^### (.*)$/gm,'<h3>$1</h3>');
    s = s.replace(/^## (.*)$/gm,'<h2>$1</h2>');
    s = s.replace(/^# (.*)$/gm,'<h1>$1</h1>');
    s = s.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>');
    s = s.replace(/\*(.+?)\*/g,'<em>$1</em>');
    s = s.replace(/`(.+?)`/g,'<code>$1</code>');
    s = s.replace(/\n/g,'<br>');
    return s;
  }
  function draw(){ box.innerHTML = md(txt.value); }
  txt.addEventListener('input', draw);
  draw();

  toggle.addEventListener('click', function(e){
    e.preventDefault();
    document.body.classList.toggle('show-preview');
    draw();
  });
})();
