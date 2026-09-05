.PHONY: check deploy

check:
	ansible-lint playbooks/

deploy:
	ansible-playbook playbooks/site.yml \
		--vault-password-file .vault_pass \
		-i inventory/hosts.yml

.PHONY: deploy-apps
deploy-apps:
	ansible-playbook playbooks/site.yml \
		--vault-password-file .vault_pass \
		-i inventory/hosts.yml \
		--tags apps,migrate

.PHONY: backup
backup:
	ansible-playbook playbooks/backup.yml \
		-i inventory/hosts.yml
