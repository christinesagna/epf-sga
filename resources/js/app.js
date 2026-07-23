const synchroniserMotifRejet = (select, effacerSiMasque = false) => {
    const formulaire = select.closest('[data-document-control]');
    const conteneurMotif = formulaire?.querySelector('[data-rejection-reason]');
    const champMotif = formulaire?.querySelector('[data-rejection-input]');

    if (!conteneurMotif || !champMotif) {
        return;
    }

    const estRejete = select.value === 'rejete';

    conteneurMotif.classList.toggle('hidden', !estRejete);
    champMotif.required = estRejete;

    if (!estRejete && effacerSiMasque) {
        champMotif.value = '';
    }
};

document.querySelectorAll('[data-document-decision]').forEach((select) => {
    synchroniserMotifRejet(select);
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-document-decision]')) {
        synchroniserMotifRejet(event.target, true);
    }
});

const synchroniserMotifDecisionJury = (select, effacerSiMasque = false) => {
    const formulaire = select.closest('[data-jury-decision-control]');
    const conteneurMotif = formulaire?.querySelector('[data-jury-decision-reason]');
    const champMotif = formulaire?.querySelector('[data-jury-decision-input]');

    if (!conteneurMotif || !champMotif) {
        return;
    }

    const estRefusee = select.value === 'refusee';

    conteneurMotif.classList.toggle('hidden', !estRefusee);
    champMotif.required = estRefusee;

    if (!estRefusee && effacerSiMasque) {
        champMotif.value = '';
    }
};

document.querySelectorAll('[data-jury-decision]').forEach((select) => {
    synchroniserMotifDecisionJury(select);
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-jury-decision]')) {
        synchroniserMotifDecisionJury(event.target, true);
    }
});
