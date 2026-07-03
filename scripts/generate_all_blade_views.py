#!/usr/bin/env python3
"""Run all Blade view generators and print total count."""
import os
import sys

sys.path.insert(0, os.path.dirname(__file__))

import generate_blade_views
import generate_blade_views_rest
import generate_blade_views_rest2
import generate_blade_views_rest3
import generate_blade_views_rest4

print(f"TOTAL_FILES_CREATED={len(generate_blade_views.created)}")
