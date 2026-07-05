document.querySelectorAll('.reaction-links').forEach(container => {
    const type = container.dataset.type;
    const id = container.dataset.id;

    container.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const value = btn.dataset.value;

            try {
                const response = await fetch(`/react/${type}/${id}/${value}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Erro na reação');

                const data = await response.json();

                const posBtn = container.querySelector('.reaction-positive');
                const negBtn = container.querySelector('.reaction-negative');

                if (posBtn) {
                    posBtn.querySelector('.reaction-count').textContent = data.positive;
                    posBtn.classList.toggle('active', data.user_reaction === 1);
                }
                if (negBtn) {
                    negBtn.querySelector('.reaction-count').textContent = data.negative;
                    negBtn.classList.toggle('active', data.user_reaction === -1);
                }

            } catch (err) {
                console.error('Erro ao reagir:', err);
            }
        });
    });
});
