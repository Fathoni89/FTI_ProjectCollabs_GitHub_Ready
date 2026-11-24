(() => {
  const navButton = document.querySelector('.nav-toggle');
  const nav = document.getElementById('primary-nav');
  if(navButton && nav){
    navButton.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      navButton.setAttribute('aria-expanded', String(open));
    });
  }
  document.querySelectorAll('.alert-close').forEach(button => {
    button.addEventListener('click', () => button.closest('.alert')?.remove());
  });
  document.querySelectorAll('[data-confirm]').forEach(button => {
    button.addEventListener('click', e => {
      if(!window.confirm(button.getAttribute('data-confirm')))e.preventDefault();
    });
  });
  const list = document.getElementById('member-list');
  const add = document.getElementById('add-member');
  const template = document.getElementById('member-template');
  if(list && add && template){
    add.addEventListener('click', () => {
      if(list.querySelectorAll('.member-row').length >= 15){window.alert('Maksimal 15 anggota.'); return;}
      list.appendChild(template.content.cloneNode(true));
      list.lastElementChild?.querySelector('input')?.focus();
    });
    list.addEventListener('click', e => {
      if(e.target instanceof Element && e.target.matches('.remove-member')){
        const item = e.target.closest('.member-row');
        if(list.querySelectorAll('.member-row').length > 1)item?.remove();
        else item?.querySelectorAll('input').forEach(input => input.value='');
      }
    });
  }
  document.querySelectorAll('[data-project-qr]').forEach((el) => {
    if(typeof window.FTIFestMakeQR === 'function')window.FTIFestMakeQR(el,el.getAttribute('data-project-qr'));
  });
  document.getElementById('print-button')?.addEventListener('click', () => window.print());
})();
