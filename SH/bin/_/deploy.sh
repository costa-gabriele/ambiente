#!/bin/bash

P_DEPLOYMENT_TYPE=${1}
separator="########"

echo ""
echo "${separator}"
echo "Loading parameters..."
. ./deployParams.sh
if [ ${?} -eq 0 ]
then
	echo "Ok"
else
	echo "An error occurred while loading the parameters."
	exit 1
fi

########

if [ -z "${P_DEPLOYMENT_TYPE}" ] || [ "${P_DEPLOYMENT_TYPE}" = "web" ]
then

	echo ""
	echo "${separator}"
	echo "Repository folder: ${WEB_REPOSITORY_SERVER}$(realpath "${WEB_REPOSITORY_DIR}")"
	echo "Target folder: $(realpath "${WEB_DEPLOYMENT_DIR}")"
	echo "${separator}"
	echo ""

	echo "Starting the deployment..."

	if [ "${WEB_REPOSITORY_SERVER}" = "localhost" ]
	then
		rm -r "${WEB_DEPLOYMENT_DIR}/"
		cp -r "${WEB_REPOSITORY_DIR}/". "${WEB_DEPLOYMENT_DIR}"
	else
		echo "sftp"
	fi

	outcome=${?}

	if [ ${outcome} -eq 0 ]
	then
		echo "Deployment succeeded."
	else
		echo "Error."
	fi

elif [ -z "${P_DEPLOYMENT_TYPE}" ] || [ "${P_DEPLOYMENT_TYPE}" = "db" ]
then

	echo ""
	echo "${separator}"
	echo "Repository folder: ${DB_REPOSITORY_SERVER}$(realpath "${DB_REPOSITORY_DIR}")"
	echo "Target folder: $(realpath "${DB_DEPLOYMENT_DIR}")"
	echo "${separator}"
	echo ""

	echo "Starting the deployment..."

	nFiles=0
	installationFile="${DB_DEPLOYMENT_DIR}/install.sql"
	cat "${DB_INSTALLATION_FILE}" | while read path || [ -n "${path}" ]
	do
		sourceFile="${DB_REPOSITORY_DIR}/${path}"
		nFiles=$(expr ${nFiles} + 1)
		if [ "${DB_REPOSITORY_SERVER}" = "localhost" ]
		then
			fileName="${nFiles}.sql"
			cp "${sourceFile}" "${DB_DEPLOYMENT_DIR}/${fileName}"
			printf "source ${fileName}\n" >> "${installationFile}"
		else
			echo "SFTP installation not supported."
			exit 1
		fi
	done

	outcome=${?}

	if [ ${outcome} -eq 0 ]
	then
		echo "Deployment succeeded."
	else
		echo "Error."
	fi

	cd "${DB_DEPLOYMENT_DIR}"
	eval $DB_CONNECTION < "${installationFile}"

fi

exit ${outcome}
