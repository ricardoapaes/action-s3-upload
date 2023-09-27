# Upload/Download file to Amazon S3 - GitHub Action

This GitHub Action upload/downloa a file to amazon s3 with the option to add metadata.

## Upload

```yaml
- name: Upload to s3
  uses: ricardoapaes/action-s3-upload@latest
  env:
    AWS_KEY: ${{ secrets.AWS_KEY }}
    AWS_SECRET: ${{ secrets.AWS_SECRET }}
  with:
    bucket: name-of-bucket
    src: filename-to-upload.zip
    filename: filename-in-bucket.zip
    metadata: var1=example1,var2=example2
    region: us-east-1
```

## Download

```yaml
- name: Download from s3
  uses: ricardoapaes/action-s3-upload/download@latest
  env:
    AWS_KEY: ${{ secrets.AWS_KEY }}
    AWS_SECRET: ${{ secrets.AWS_SECRET }}
  with:
    bucket: name-of-bucket
    src: file-to-upload.zip
    dest: path-in-bucket.zip
    region: us-east-1
```
