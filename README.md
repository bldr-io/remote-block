remote-block
============

Remote Execution with bldr


To use
======

First, add the following to your `bldr.json` in the `require` section:

```js
{
    // ...
    "require": {
        // ...
        "bldr-io/remote-block": "~1.0.0"
    }
}
```

Then, configure your hosts in your `.bldr.yml(.dist)` file:

```yaml
remote:
    someHost:
        hostname: example.org
        port:     2222
        username: testUser
        password: testPass
    vagrantHost:
        hostname: 192.168.56.101
        username: vagrant
        rsa_key:  puphpet/files/dot/ssh/insecure_private_key
    rsaWithPass:
        hostname: example.org
        username: someUser
        password: somePass
        rsa_key: /home/someUser/.ssh/id_rsa

bldr:
    tasks:
        someTask:
            calls:
                -
                    type: exec
                    remote: vagrantHost
                    executable: ls
                    arguments: [-lha, /var/www]
```

Right now, you wont get output, unless you run bldr with `-vvv`. If people want this to change, I'd be happy to add it in.