FROM node:16-bullseye-slim
ARG NODE_ENV=production
ENV NODE_ENV $NODE_ENV
RUN apt-get update > /dev/null && apt-get install -y vim htop psmisc --fix-missing > /dev/null
RUN yarn global add expo-cli
RUN chown -R node /home/node/
USER node
WORKDIR /home/node/app
ENTRYPOINT ["sleep", "999999999999999"]
# ENTRYPOINT ["yarn", "run"]
# CMD ["web"]
