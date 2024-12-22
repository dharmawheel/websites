.PHONY = check
check:
	packer init ./dw-mono.pkr.hcl
	packer validate ./dw-mono.pkr.hcl
	ansible-lint playbooks/
	(cd terraform; \
	terraform init; \
	terraform validate)

.PHONY = fmt
fmt:
	yamlfmt playbooks/
	packer fmt ./dw-mono.pkr.hcl

manifest.json:
	packer validate ./dw-mono.pkr.hcl
	packer build ./dw-mono.pkr.hcl

.PHONY = deploy
deploy: manifest.json
	$(eval AMI_ID := $(shell jq -r '.builds[-1].artifact_id' manifest.json | cut -d':' -f2))
	(cd terraform; \
	terraform init; \
	terraform validate; \
	terraform apply -auto-approve -var "ami_id=$(AMI_ID)")
	aws autoscaling start-instance-refresh --auto-scaling-group-name AppServerAutoscalingGroup

.PHONY = clean
clean:
	rm -f manifest.json
