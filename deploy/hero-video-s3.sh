#!/usr/bin/env bash
# Compress the landing hero video, upload to S3, and make video/* public.
# Usage: AWS_ACCESS_KEY_ID=... AWS_SECRET_ACCESS_KEY=... bash hero-video-s3.sh
set -euo pipefail

export AWS_DEFAULT_REGION=eu-north-1
BUCKET=expresscarwash-media
SRC=/var/www/expresscarwash/public/video/bg.mp4
WORK=/tmp/hero-video
mkdir -p "$WORK"

echo "== ffmpeg =="
if ! command -v ffmpeg >/dev/null; then
    sudo apt-get install -y -qq ffmpeg >/dev/null
fi
ffmpeg -version | head -1

echo "== Temporary swap (server has no headroom for encoding) =="
if [ ! -f /swapfile-tmp ]; then
    sudo fallocate -l 2G /swapfile-tmp
    sudo chmod 600 /swapfile-tmp
    sudo mkswap /swapfile-tmp >/dev/null
    sudo swapon /swapfile-tmp
fi

cleanup_swap() {
    sudo swapoff /swapfile-tmp 2>/dev/null || true
    sudo rm -f /swapfile-tmp
}
trap cleanup_swap EXIT

echo "== Compress (720p, no audio, faststart) =="
ffmpeg -y -loglevel error -threads 1 -i "$SRC" \
    -vf "scale=1280:-2" -c:v libx264 -crf 28 -preset veryfast \
    -an -movflags +faststart "$WORK/bg-720.mp4"

echo "== Poster frame =="
ffmpeg -y -loglevel error -i "$SRC" -vframes 1 -q:v 4 -vf "scale=1280:-2" "$WORK/bg-poster.jpg"

ls -lh "$WORK"

echo "== Allow public bucket policy on video/* =="
aws s3api put-public-access-block --bucket "$BUCKET" \
    --public-access-block-configuration BlockPublicAcls=true,IgnorePublicAcls=true,BlockPublicPolicy=false,RestrictPublicBuckets=false

aws s3api put-bucket-policy --bucket "$BUCKET" --policy '{
    "Version": "2012-10-17",
    "Statement": [{
        "Sid": "PublicReadHeroVideo",
        "Effect": "Allow",
        "Principal": "*",
        "Action": "s3:GetObject",
        "Resource": "arn:aws:s3:::expresscarwash-media/video/*"
    }]
}'

echo "== Upload =="
aws s3 cp "$WORK/bg-720.mp4" "s3://$BUCKET/video/bg-720.mp4" \
    --cache-control "public,max-age=31536000,immutable" --content-type video/mp4
aws s3 cp "$WORK/bg-poster.jpg" "s3://$BUCKET/video/bg-poster.jpg" \
    --cache-control "public,max-age=31536000,immutable" --content-type image/jpeg

echo "== Verify public access =="
curl -s -o /dev/null -w "video: HTTP %{http_code}, %{size_download} bytes\n" \
    "https://$BUCKET.s3.eu-north-1.amazonaws.com/video/bg-720.mp4"
curl -s -o /dev/null -w "poster: HTTP %{http_code}\n" \
    "https://$BUCKET.s3.eu-north-1.amazonaws.com/video/bg-poster.jpg"

rm -rf "$WORK"
echo "== Done =="
