(function(){
  const root = document.querySelector('[data-ccc]');
  if(!root || typeof CCC_DATA === 'undefined') return;

  const fmt = (n) => {
    const s = CCC_DATA.settings;
    return new Intl.NumberFormat('en-US', { style:'currency', currency:s.currency, minimumFractionDigits:s.decimals, maximumFractionDigits:s.decimals }).format(n);
  };

  const byMonth = (perWeek) => perWeek * 4; // 4 weeks per month

  const findPackage = (duration, perMonth) => {
    // packages.json structure:
    // { "25": { "4": {"name":"Beginner","price":49.99}, "8": {...}, ... }, "50": { ... } }
    const d = String(duration);
    const m = String(perMonth);
    const all = CCC_DATA.packages[d] || {};
    return all[m] || null;
  };

  const calcDiscount = (base) => {
    const tiers = CCC_DATA.settings.discounts || [];
    for (let i=0; i<tiers.length; i++) {
      const t = tiers[i];
      if (base >= t.threshold) return t.percent;
    }
    return 0;
  };

  const ui = {
    tabs: root.querySelectorAll('.ccc-tab'),
    cards: root.querySelectorAll('.ccc-card'),
    lblDuration: root.querySelector('[data-duration-label]'),
    lblPerWeek: root.querySelector('[data-perweek]'),
    lblPerMonth: root.querySelector('[data-permonth]'),
    lblPackage: root.querySelector('[data-package]'),
    oldPrice: root.querySelector('[data-oldprice]'),
    newPrice: root.querySelector('[data-newprice]'),
    discountBadge: root.querySelector('[data-discountbadge]'),
    subscribe: root.querySelector('[data-subscribe]')
  };

  let state = { duration:25, perWeek:1 };

  // init colors
  (function setColors(){
    const c = CCC_DATA.settings.colors || {};
    const css = `
      .ccc-wrap .ccc-btn{background:${c.primary||'#EDA01B'}}
      .ccc-wrap .ccc-card.is-active{border-color:${c.primary||'#EDA01B'}; color:${c.dark||'#4B3F33'}}
      .ccc-wrap .ccc-tab.is-active{background:${c.dark||'#4B3F33'}; color:#fff}
      .ccc-wrap{--ccc-paper:${c.paper||'#EFECE8'}; --ccc-dark:${c.dark||'#4B3F33'}; --ccc-primary:${c.primary||'#EDA01B'}}
    `;
    const el = document.createElement('style');
    el.textContent = css;
    document.head.appendChild(el);
  })();

  const render = () => {
    const perMonth = byMonth(state.perWeek);
    ui.lblDuration.textContent = state.duration + ' min';
    ui.lblPerWeek.textContent = state.perWeek;
    ui.lblPerMonth.textContent = perMonth;

    const pkg = findPackage(state.duration, perMonth);
    if (!pkg) {
      ui.lblPackage.textContent = 'N/A';
      ui.oldPrice.textContent = fmt(0);
      ui.newPrice.textContent = fmt(0);
      ui.discountBadge.textContent = '';
      ui.subscribe.setAttribute('href', '#');
      return;
    }

    ui.lblPackage.textContent = pkg.name || 'Package';
    const base = Number(pkg.price||0);
    const pct = calcDiscount(base);
    const after = base * (1 - pct/100);
    ui.oldPrice.textContent = fmt(base);
    ui.newPrice.textContent = fmt(after);
    ui.discountBadge.textContent = pct ? `(-${pct}%)` : '';

    // subscribe link key: "<duration>m_<perMonth>"
    const key = `${state.duration}m_${perMonth}`;
    const link = (CCC_DATA.settings.subscribe_links||{})[key] || '#';
    ui.subscribe.setAttribute('href', link);
  };

  // interactions
  ui.tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      ui.tabs.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      state.duration = Number(btn.dataset.duration);
      render();
    });
  });

  ui.cards.forEach(card => {
    card.addEventListener('click', () => {
      if(card.classList.contains('is-disabled')) return;
      ui.cards.forEach(c => c.classList.remove('is-active'));
      card.classList.add('is-active');
      state.perWeek = Number(card.dataset.perweek);
      render();
    });
  });

  // set defaults
  ui.tabs[0].classList.add('is-active');
  ui.cards[0].classList.add('is-active');
  render();
})();