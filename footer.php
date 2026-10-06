</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function formatRupiahInput(input, preserveCaret = true){
  const original = input.value;
  const caret = preserveCaret && typeof input.selectionStart === 'number' ? input.selectionStart : original.length;
  const digitsBeforeCaret = original.slice(0, caret).replace(/\D/g, '').length;
  const digits = original.replace(/\D/g, '');
  const formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  input.value = formatted;
  if(preserveCaret && document.activeElement === input){
    let position = 0;
    let digitsSeen = 0;
    while(position < formatted.length && digitsSeen < digitsBeforeCaret){
      if(/\d/.test(formatted[position])) digitsSeen++;
      position++;
    }
    input.setSelectionRange(position, position);
  }
}

document.querySelectorAll('.rupiah-input').forEach((input) => formatRupiahInput(input, false));
document.addEventListener('input', (event) => {
  if(event.target.matches('.rupiah-input')) formatRupiahInput(event.target);
});
document.addEventListener('focusout', (event) => {
  if(event.target.matches('.rupiah-input')) formatRupiahInput(event.target, false);
});

function ensureModal(){
  let modalEl = document.getElementById('addedModal');
  if(!modalEl){
    document.body.insertAdjacentHTML('beforeend', `
      <div class="modal fade" id="addedModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Ditambahkan!</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Produk masuk ke keranjang.</div>
            <div class="modal-footer">
              <a href="cart.php" class="btn btn-primary">Lihat Keranjang</a>
              <button class="btn btn-primary" data-bs-dismiss="modal">Lanjut Belanja</button>
            </div>
          </div>
        </div>
      </div>
    `);
    modalEl = document.getElementById('addedModal');
  }
  return modalEl;
}
async function addToCart(productId, size, qty){
  try{
    const res = await fetch('add_to_cart.php',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({product_id: productId, size: size, qty: qty})
    });
    if(!res.ok) throw new Error('HTTP '+res.status);
    const data = await res.json();
    const modalEl = ensureModal();
    modalEl.querySelector('.modal-body').innerHTML = data.message || 'Produk masuk ke keranjang.';
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    const nav = document.querySelector('a.nav-link[href$="cart.php"]');
    if(typeof data.total_items !== 'undefined' && nav){
      nav.innerHTML = '<i class="fa fa-shopping-cart"></i> Keranjang ('+data.total_items+')';
    }
  }catch(err){
    console.error(err);
    alert('Gagal menambah ke keranjang. Coba refresh (Ctrl+F5).');
  }
}
function selectSize(pid, el, size){
  const hidden = document.querySelector('input[name="size_'+pid+'"]');
  if (hidden) hidden.value = size;

  const container = el.closest('.mb-2');
  if (container){
    container.querySelectorAll('.badge-size').forEach(b=>{
      b.classList.remove('is-selected','bg-dark','text-white','bg-light','text-dark');
      b.setAttribute('aria-pressed', 'false');
    });
  }

  el.classList.remove('bg-light','text-dark','bg-dark','text-white');
  el.classList.add('is-selected','bg-dark','text-white');
  el.setAttribute('aria-pressed', 'true');
}
</script>

<!-- Modal fallback (exists in DOM) -->
<div class="modal fade" id="addedModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ditambahkan!</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Produk masuk ke keranjang.</div>
      <div class="modal-footer">
        <a href="cart.php" class="btn btn-primary">Lihat Keranjang</a>
        <button class="btn btn-primary" data-bs-dismiss="modal">Lanjut Belanja</button>
      </div>
    </div>
  </div>
</div>
</body></html>
