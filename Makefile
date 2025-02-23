.PHONY = check
check:
	packer init ./dw-mono-base.pkr.hcl
	packer validate ./dw-mono-base.pkr.hcl
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

dw_mono_base_manifest.json:
	packer validate ./dw-mono-base.pkr.hcl
	packer build ./dw-mono-base.pkr.hcl

dw_mono_manifest.json: dw_mono_base_manifest.json
	packer validate ./dw-mono.pkr.hcl
	packer build -var="source_ami=$(shell jq -r '.builds[-1].artifact_id' dw_mono_base_manifest.json | cut -d':' -f2)" ./dw-mono.pkr.hcl

.PHONY = deploy
deploy: dw_mono_manifest.json
	$(eval AMI_ID := $(shell jq -r '.builds[-1].artifact_id' dw_mono_manifest.json | cut -d':' -f2))
	(cd terraform; \
	terraform init; \
	terraform validate; \
	terraform apply -auto-approve -var "ami_id=$(AMI_ID)")
	aws autoscaling start-instance-refresh --auto-scaling-group-name AppServerAutoscalingGroup

.PHONY = clean
clean:
	rm -f dw_mono_base_manifest.json
	rm -f dw_mono_manifest.json
