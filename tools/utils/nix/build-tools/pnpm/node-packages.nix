# This file has been generated by node2nix 1.11.1. Do not edit!

{nodeEnv, fetchurl, fetchgit, nix-gitignore, stdenv, lib, globalBuildInputs ? []}:

let
  sources = {};
in
{
  "pnpm-^8" = nodeEnv.buildNodePackage {
    name = "pnpm";
    packageName = "pnpm";
    version = "8.7.0";
    src = fetchurl {
      url = "https://registry.npmjs.org/pnpm/-/pnpm-8.7.0.tgz";
      sha512 = "HWH4wQ6KWl2/vd6g8fXvt9vVF3IjBzrslTzyMKpGQWiEuUJ6ZCHbp48orQ+T++3ji6VwgyN7NQJD3mseOoznHQ==";
    };
    buildInputs = globalBuildInputs;
    meta = {
      description = "Fast, disk space efficient package manager";
      homepage = "https://pnpm.io";
      license = "MIT";
    };
    production = true;
    bypassCache = true;
    reconstructLock = true;
  };
}
