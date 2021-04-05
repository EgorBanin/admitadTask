db.users.createIndex({token: 1}, {unique: true})
db.links.createIndex({key: 1}, {unique: true})