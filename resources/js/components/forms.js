const FormHandler = (params = {}) => ({
    params: params,
    success: false,
    submitted: false,
    form: null,
    init() {
        this.form = this.$form(
            'post',
            this.$refs.form.getAttribute('action'),
            JSON.parse(this.$refs.form.getAttribute('x-data')).form,
            {
                headers: {
                    'X-CSRF-Token': {
                        toString: () => this.$refs.form.querySelector('[name="_token"]').value,
                    }
                }
            }
        );

        if (params.errors) {
            this.form.setErrors(params.errors);
        }
    },
    submit() {
        if (this.params.beforeSubmit ?? false) {
            this.params.beforeSubmit(this);

            return;
        }

        this.submitWithoutInterception();
    },
    submitWithoutInterception() {
        this.submitted = true

        if (this.params.nativeSubmit ?? false) {
            this.$refs.form.requestSubmit();

            return;
        }

        this.form.submit()
            .then(response => {
                if (this.params.shouldReset ?? true) {
                    this.form.reset()
                }

                this.form.errors = [];
                this.$refs.form.reset()
                this.success = true
                this.submitted = false

                if (this.params.onSuccess) {
                    this.params.onSuccess(this);
                }
            })
            .then(() => { })
            .catch(error => {
                if (this.params.onError) {
                    this.params.onError(this);
                }

                if (! Object.values(this.form.errors).length) {
                    if (error.response?.data?.error) {
                        this.form.errors = error.response.data.error;
                        return;
                    }

                    this.form.errors['_unspecified'] = error.response?.data?.message ?? error.message;
                }
            });
    }
});

export default FormHandler;
