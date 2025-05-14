#!/bin/sh

fileDir=$(dirname "${0}")
sourceDir=$(realpath "${fileDir}/../../../")
excludeFile=$(realpath "${fileDir}/../../etc/_/.installIgnore.txt")
installationType="i" # i = initial (complete, default) / u = update (exclude configurations)

echo "Source directory: ${sourceDir}"
echo "Exclude file: ${excludeFile}"

separator="########"

if [ "${#}" -lt 1 ]
then
	echo "The installation directory must be provided to the script as the first argument."
	exit 1
fi

targetDir=$(realpath "${1}")

if [ "${?}" -ne 0 ]
then
	echo "The project directory provided doesn't exist."
	exit 1
fi


if [ ! -z ${2} ]
then
	installationType="${2}"
fi

if [ $installationType != "i" && $installationType != "u" ]
then
	echo "Invalid installation type."
	exit 1
fi

echo ""
echo "${separator}"
echo "Installation directory: $(realpath "${targetDir}")"
echo "${separator}"
echo ""

echo "Starting the installation..."

echo "Installation type: "
if [ $installationType = "i" ]
then
	echo "initial"
	rsync -av --exclude "*/.install.sh" --exclude ".git" "${sourceDir}/". "${targetDir}"
else
	echo "update"
	rsync -av --exclude "*/.install.sh" --exclude ".git" --exclude-from="${excludeFile}" "${sourceDir}/". "${targetDir}"
fi

outcome=${?}

if [ ${outcome} -eq 0 ]
then
	echo "Installation completed."
else
	echo "Error."
fi

exit ${outcome}
